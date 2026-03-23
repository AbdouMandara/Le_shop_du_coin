<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\HTTP\Requests\ReviewRequest;
use App\Http\Resources\ReviewResource;

class ReviewController extends Controller
{
    public function store(ReviewRequest $request)
    {
        $valided_data = $request->validate();

        $review = Review::create($valided_data);

        return response()->json([
            'success' => true,
            'data' => new ReviewResource($review),
        ]);
    }
}
