<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function index()
    {
        return response()->json([
            'products_count' => Product::count(),
            'clients_count' => User::whereHas('role', function($q) {
                $q->where('label', 'user');
            })->count(),
            'livreurs_count' => User::whereHas('role', function($q) {
                $q->where('label', 'livreur');
            })->count(),
            'orders_count' => Order::count(),
        ]);
    }
}
