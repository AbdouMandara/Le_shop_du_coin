<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\ReviewResource;

class ReviewController extends Controller
{
    public function store(ReviewRequest $request)
    {
        $review = Review::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => new ReviewResource($review),
        ]);
    }
}
