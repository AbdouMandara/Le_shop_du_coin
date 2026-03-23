<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->favorites()->with('product')->get();
    }

    public function store(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);
        
        $favorite = Favorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        return response()->json($favorite, 21);
    }

    public function destroy(Favorite $favorite)
    {
        if ($favorite->user_id !== auth()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $favorite->delete();
        return response()->json(null, 24);
    }
}
