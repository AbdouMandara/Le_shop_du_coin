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
        if ($request->user()->role->label === 'admin') {
            return OrderResource::collection(Order::with(['user', 'product'])->latest()->paginate(10));
        }
        
        if ($request->user()->role->label === 'livreur') {
            return OrderResource::collection(Order::whereIn('status', ['paid', 'delivered'])->with(['user', 'product'])->latest()->paginate(10));
        }

        return OrderResource::collection($request->user()->orders()->with('product')->latest()->get());
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
