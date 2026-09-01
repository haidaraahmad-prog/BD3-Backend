<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cartId' => ['nullable', 'uuid', 'exists:carts,id'],
            'productId' => ['required', 'string', 'exists:products,slug'],
            'colorId' => ['required', 'string', 'exists:colors,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9'],
        ]);

        $product = Product::query()->with('colors')->findOrFail($validated['productId']);

        if (! $product->colors->contains('id', $validated['colorId'])) {
            return response()->json(['message' => 'Color not available for this product.'], 422);
        }

        $cart = isset($validated['cartId'])
            ? Cart::query()->findOrFail($validated['cartId'])
            : Cart::query()->create([]);

        $item = CartItem::query()->updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_slug' => $product->slug,
                'color_id' => $validated['colorId'],
            ],
            ['quantity' => $validated['quantity']],
        );

        return response()->json([
            'cartId' => $cart->id,
            'item' => [
                'id' => $item->id,
                'productId' => $item->product_slug,
                'colorId' => $item->color_id,
                'quantity' => $item->quantity,
            ],
        ], 201);
    }

    public function show(string $cartId): JsonResponse
    {
        $cart = Cart::query()->with(['items.product', 'items.color'])->find($cartId);

        if (! $cart) {
            return response()->json(['message' => 'Cart not found.'], 404);
        }

        return response()->json([
            'id' => $cart->id,
            'items' => $cart->items->map(fn (CartItem $item) => [
                'id' => $item->id,
                'productId' => $item->product_slug,
                'productName' => $item->product->name,
                'colorId' => $item->color_id,
                'colorLabel' => $item->color->label,
                'quantity' => $item->quantity,
                'unitPrice' => $item->product->price,
                'lineTotal' => $item->product->price * $item->quantity,
            ]),
            'subtotal' => $cart->items->sum(fn (CartItem $item) => $item->product->price * $item->quantity),
        ]);
    }
}
