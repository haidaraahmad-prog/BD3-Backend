<?php

use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\ColorController as AdminColorController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\FavouriteController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'service' => 'bd3-api',
]));

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/catalog', [CatalogController::class, 'index']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::get('/products/{slug}/gallery', [ProductController::class, 'gallery']);
Route::get('/products/{slug}/copy', [ProductController::class, 'copy']);

Route::post('/cart/items', [CartController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/cart', [CartController::class, 'mine']);

    Route::get('/favourites', [FavouriteController::class, 'index']);
    Route::post('/favourites', [FavouriteController::class, 'store']);
    Route::delete('/favourites/{productSlug}', [FavouriteController::class, 'destroy']);
});

Route::get('/cart/{cartId}', [CartController::class, 'show']);

Route::post('/checkout/pay', [CheckoutController::class, 'pay']);
Route::get('/checkout/orders/{reference}', [CheckoutController::class, 'show']);
Route::post('/webhooks/checkout', [CheckoutController::class, 'webhook']);

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/me', [AdminAuthController::class, 'me']);
        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::get('/products', [AdminProductController::class, 'index']);
        Route::post('/products', [AdminProductController::class, 'store']);
        Route::get('/products/{slug}', [AdminProductController::class, 'show']);
        Route::put('/products/{slug}', [AdminProductController::class, 'update']);
        Route::delete('/products/{slug}', [AdminProductController::class, 'destroy']);

        Route::get('/colors', [AdminColorController::class, 'index']);
        Route::post('/colors', [AdminColorController::class, 'store']);
        Route::put('/colors/{id}', [AdminColorController::class, 'update']);
        Route::delete('/colors/{id}', [AdminColorController::class, 'destroy']);
    });
});
