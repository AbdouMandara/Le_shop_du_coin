<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\OrderUpdateRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\UserResource;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Order::with(['user', 'product', 'livreur'])->latest();

        if (strtolower($user->role->label) === 'livreur') {
            $query->where('livreur_id', $user->id);
        } elseif (strtolower($user->role->label) === 'user' || strtolower($user->role->label) === 'client') {
            $query->where('user_id', $user->id);
        }

        // Filters for Admin and Livreur
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date') && $request->date) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->has('assignment') && $request->assignment) {
            if ($request->assignment === 'assigned') {
                $query->whereNotNull('livreur_id');
            } elseif ($request->assignment === 'unassigned') {
                $query->whereNull('livreur_id')->where('delivery', true);
            } elseif ($request->assignment === 'ordered') {
                $query->whereNull('livreur_id')->where('delivery', false);
            }
        }

        // Return all for client, paginated for others
        if ($user->role->label === 'client') {
            return OrderResource::collection($query->get());
        }

        return OrderResource::collection($query->paginate(10));
    }

    public function store(OrderRequest $request)
    {
        $donnees_validees = $request->validated();
        $donnees_validees['user_id'] = $request->user()->id;
        
        $product = Product::findOrFail($donnees_validees['product_id']);
        $donnees_validees['price_at_purchase'] = $product->final_price;
        
        $order = Order::create($donnees_validees);

        return response()->json([
            'success' => true, 
            'data' =>new OrderResource($order)
            ], 201);
    }

    public function update(OrderUpdateRequest $request, Order $order)
    {
        $user = $request->user();
        
        // Admin or Livreur can update status
        // Client can update status ONLY if it's their own order and the new status is 'delivered' or 'picked_up'
        $isAuthorized = in_array($user->role->label, ['admin', 'livreur']) || 
                      ($user->role->label === 'client' && $order->user_id === $user->id && in_array($request->status, ['delivered', 'picked_up']));

        if (!$isAuthorized) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $oldStatus = $order->status;
        $order->update($request->validated());

        // Stock Decrement Logic
        // If transitioning to a final state (delivered or picked_up) AND not already there
        if (in_array($order->status, ['delivered', 'picked_up']) && !in_array($oldStatus, ['delivered', 'picked_up'])) {
            $product = $order->product;
            if ($product) {
                // Ensure we don't go below 0 (optional based on preference, but safe)
                $product->decrement('quantity', 1);
            }
        }

        // Notify client about status change
        if ($request->has('status')) {
            $messages = [
                'in_transit' => 'Votre commande est en cours de livraison.',
                'delivered' => 'Votre commande a été livrée.',
                'cancelled' => 'Votre commande a été annulée.'
            ];
            
            if (isset($messages[$request->status]) && $order->user) {
                // De-duplicate: don't create the same notification if it was created in the last minute
                $alreadyNotified = $order->user->notifications()
                    ->where('message', $messages[$request->status])
                    ->where('created_at', '>=', now()->subMinute())
                    ->exists();

                if (!$alreadyNotified) {
                    $order->user->notifications()->create([
                        'message' => $messages[$request->status],
                        'type' => 'info',
                    ]);
                }
            }
        }

        return new OrderResource($order);
    }

    public function bulkStatusUpdate(Request $request)
    {
        $user = $request->user();
        if (!in_array($user->role->label, ['admin', 'livreur'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:orders,id',
            'status' => 'required|string',
        ]);

        $status = $validatedData['status'];
        $ids = $validatedData['ids'];

        // Get orders with users to notify and products for stock
        $orders = Order::whereIn('id', $ids)->with(['user', 'product'])->get();
        
        // Update all statuses
        // Note: For bulk update, we check if we need to decrement stock for each order
        $messages = [
            'in_transit' => 'Votre commande est en cours de livraison.',
            'delivered' => 'Votre commande a été livrée.',
            'picked_up' => 'Votre commande a été récupérée.',
            'cancelled' => 'Votre commande a été annulée.'
        ];

        foreach ($orders as $order) {
            $oldStatus = $order->status;
            if ($oldStatus !== $status) {
                $order->status = $status;
                $order->save();
                
                // Decrement stock if transitioning to final state
                if (in_array($status, ['delivered', 'picked_up']) && !in_array($oldStatus, ['delivered', 'picked_up'])) {
                    if ($order->product) {
                        $order->product->decrement('quantity', 1);
                    }
                }
            }
        }

        if (isset($messages[$status])) {
            // Group by user_id to send only ONE notification per client
            $userIds = $orders->pluck('user_id')->unique();
            
            foreach ($userIds as $userId) {
                if ($userId) {
                    \App\Models\Notification::create([
                        'user_id' => $userId,
                        'message' => $messages[$status],
                        'type' => 'info',
                    ]);
                }
            }
        }

        return response()->json(['message' => 'Statuts mis à jour avec succès.', 'updated_count' => count($ids)]);
    }

    public function assignLivreur(Request $request, Order $order)
    {
        $user = $request->user();
        if ($user->role->label !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'livreur_id' => 'required|exists:users,id',
        ]);

        $order->update([
            'livreur_id' => $validatedData['livreur_id'],
        ]);

        // Create notification for the client
        $order->user->notifications()->create([
            'message' => 'Votre commande a été prise en compte',
            'type' => 'info',
        ]);

        return new OrderResource($order);
    }

    public function downloadInvoice(Order $order)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('order'));
        return $pdf->download("facture-{$order->id}.pdf");
    }
}
