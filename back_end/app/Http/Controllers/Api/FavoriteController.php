<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\Request;
use App\Http\Requests\FavoriteRequest;
use App\Http\Resources\FavoriteResource;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        return FavoriteResource::collection($request->user()->favorites()->with('product')->get());
    }

    public function store(FavoriteRequest $request)
    {
        $favorite = Favorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $request->validated()['product_id'],
        ]);

        return response()->json(new FavoriteResource($favorite), 201);
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
