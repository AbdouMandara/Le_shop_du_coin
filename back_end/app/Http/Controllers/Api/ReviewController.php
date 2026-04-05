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
        $validatedData = $request->validated();
        $validatedData['user_id'] = $request->user()->id;
        
        $review = Review::create($validatedData);

        return response()->json([
            'success' => true,
            'data' => new ReviewResource($review),
        ]);
    }
}
