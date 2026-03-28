<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
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

        if ($user->role->label === 'livreur') {
            $query->where('livreur_id', $user->id);
        } elseif ($user->role->label === 'client') {
            $query->where('user_id', $user->id);
        }

        // Filters for Admin and Livreur
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date') && $request->date) {
            $query->whereDate('created_at', $request->date);
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
        if (!in_array($user->role->label, ['admin', 'livreur'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->update($request->validated());

        // Notify client about status change
        if ($request->has('status')) {
            $messages = [
                'in_transit' => 'Votre commande est en cours de livraison.',
                'delivered' => 'Votre commande a été livrée.',
                'cancelled' => 'Votre commande a été annulée.'
            ];
            
            if (isset($messages[$request->status]) && $order->user) {
                $order->user->notifications()->create([
                    'message' => $messages[$request->status],
                    'type' => 'info',
                ]);
            }
        }

        return new OrderResource($order);
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
