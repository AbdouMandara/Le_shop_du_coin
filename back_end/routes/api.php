<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StatsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


// Profile routes — accessible by all authenticated users regardless of role
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);
});

// Public routes
Route::post('/newsletter/subscribe', [\App\Http\Controllers\Api\NewsletterController::class, 'store']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/promotional', [ProductController::class, 'promotional']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return new \App\Http\Resources\UserResource($request->user()->load('role'));
});

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::apiResource('users', \App\Http\Controllers\Api\UserController::class);
    Route::patch('/users/{user}/toggle-active', [\App\Http\Controllers\Api\UserController::class, 'toggleActive']);
    Route::get('/roles', function () {
        return response()->json(\App\Models\Role::all());
    });
    Route::apiResource('products', ProductController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::get('/stats', [StatsController::class, 'index']);
    Route::get('/orders', [\App\Http\Controllers\Api\OrderController::class, 'index']);
    Route::patch('/orders/bulk-status-update', [\App\Http\Controllers\Api\OrderController::class, 'bulkStatusUpdate']);
    Route::patch('/orders/{order}', [\App\Http\Controllers\Api\OrderController::class, 'update']);
    Route::patch('/orders/{order}/assign', [\App\Http\Controllers\Api\OrderController::class, 'assignLivreur']);
    
    Route::apiResource('promotions', PromotionController::class);
    Route::post('/products/{product}/promotions/{promotion}', [PromotionController::class, 'assignToProduct']);
    Route::delete('/products/{product}/promotions/{promotion}', [PromotionController::class, 'removeFromProduct']);
    Route::get('/newsletters', [\App\Http\Controllers\Api\NewsletterController::class, 'index']);
    Route::get('/orders/{order}/invoice', [\App\Http\Controllers\Api\OrderController::class, 'downloadInvoice']);
});

// Livreur routes
Route::middleware(['auth:sanctum', 'role:livreur'])->prefix('livreur')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/orders', [\App\Http\Controllers\Api\OrderController::class, 'index']);
    Route::patch('/orders/bulk-status-update', [\App\Http\Controllers\Api\OrderController::class, 'bulkStatusUpdate']);
    Route::patch('/orders/{order}', [\App\Http\Controllers\Api\OrderController::class, 'update']);
    Route::get('/orders/{order}/invoice', [\App\Http\Controllers\Api\OrderController::class, 'downloadInvoice']);
});

// User (Client) routes
Route::middleware(['auth:sanctum', 'role:user'])->prefix('client')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{category}', [CategoryController::class, 'show']);

    
    Route::get('/orders', [\App\Http\Controllers\Api\OrderController::class, 'index']);
    Route::patch('/orders/bulk-status-update', [\App\Http\Controllers\Api\OrderController::class, 'bulkStatusUpdate']);
    Route::post('/orders', [\App\Http\Controllers\Api\OrderController::class, 'store']);
    Route::get('/orders/{order}/invoice', [\App\Http\Controllers\Api\OrderController::class, 'downloadInvoice']);

    Route::post('/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'store']);

    Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
    Route::patch('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAsRead']);
});
