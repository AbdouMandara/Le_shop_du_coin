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
        $query = Order::with(['user', 'product'])->latest();

        if ($user->role->label === 'livreur') {
            $query->whereIn('status', ['paid', 'delivered']);
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

        return new OrderResource($order);
    }

    public function downloadInvoice(Order $order)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('order'));
        return $pdf->download("facture-{$order->id}.pdf");
    }
}
