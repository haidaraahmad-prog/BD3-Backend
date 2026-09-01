<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Color;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $recentProducts = Product::query()
            ->orderByDesc('released_at')
            ->limit(5)
            ->get(['slug', 'name', 'price', 'is_new', 'released_at']);

        return response()->json([
            'stats' => [
                'products' => Product::query()->count(),
                'colors' => Color::query()->count(),
                'carts' => Cart::query()->count(),
                'cartItems' => CartItem::query()->count(),
                'newProducts' => Product::query()->where('is_new', true)->count(),
            ],
            'recentProducts' => $recentProducts->map(fn (Product $p) => [
                'id' => $p->slug,
                'name' => $p->name,
                'price' => $p->price,
                'isNew' => $p->is_new,
                'createdAt' => $p->released_at->format('Y-m-d'),
            ]),
        ]);
    }
}
