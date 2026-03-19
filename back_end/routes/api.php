<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/user', function (Request $request) {
    return $request->user()->load('role');
})->middleware('auth:sanctum');

// Public routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user()->load('role');
});

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/users', [\App\Http\Controllers\Api\UserController::class, 'index']);
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
    Route::get('/orders', [\App\Http\Controllers\Api\OrderController::class, 'index']);
    Route::patch('/orders/{order}', [\App\Http\Controllers\Api\OrderController::class, 'update']);
});

// Livreur routes
Route::middleware(['auth:sanctum', 'role:livreur'])->prefix('livreur')->group(function () {
    Route::get('/orders', [\App\Http\Controllers\Api\OrderController::class, 'index']);
    Route::patch('/orders/{order}', [\App\Http\Controllers\Api\OrderController::class, 'update']);
});

// User (Client) routes
Route::middleware(['auth:sanctum', 'role:user'])->prefix('client')->group(function () {
    Route::apiResource('favorites', \App\Http\Controllers\Api\FavoriteController::class);
    
    Route::get('/orders', [\App\Http\Controllers\Api\OrderController::class, 'index']);
    Route::post('/orders', [\App\Http\Controllers\Api\OrderController::class, 'store']);
    Route::get('/orders/{order}/invoice', [\App\Http\Controllers\Api\OrderController::class, 'downloadInvoice']);

    Route::post('/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'store']);
});

