<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Color;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'products' => Product::query()->count(),
            'colors' => Color::query()->count(),
            'carts' => Cart::query()->count(),
            'cartItems' => CartItem::query()->count(),
            'newProducts' => Product::query()->where('is_new', true)->count(),
        ];

        $recentProducts = Product::query()
            ->orderByDesc('released_at')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentProducts'));
    }
}
