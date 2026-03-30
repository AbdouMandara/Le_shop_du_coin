<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromotionRequest;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index()
    {
        return response()->json(Promotion::latest()->get());
    }

    public function store(PromotionRequest $request)
    {
        if ($request->user()->role->label !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $promotion = Promotion::create($request->validated());

        return response()->json($promotion, 201);
    }

    public function update(PromotionRequest $request, Promotion $promotion)
    {
        if ($request->user()->role->label !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $promotion->update($request->validated());

        return response()->json($promotion);
    }

    public function destroy(Promotion $promotion)
    {
        $promotion->delete();

        return response()->json(null, 204);
    }

    public function assignToProduct(Product $product, Promotion $promotion)
    {
        $product->promotions()->syncWithoutDetaching([$promotion->id]);

        return response()->json(['message' => 'Promotion assigned successfully']);
    }

    public function removeFromProduct(Product $product, Promotion $promotion)
    {
        $product->promotions()->detach($promotion->id);

        return response()->json(['message' => 'Promotion removed successfully']);
    }
}
