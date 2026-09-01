<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function pay(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cartId' => ['required', 'uuid', 'exists:carts,id'],
        ]);

        $cart = Cart::query()->with(['items.product'])->findOrFail($validated['cartId']);

        if ($cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty.'], 422);
        }

        $subtotal = $cart->items->sum(fn ($item) => $item->product->price * $item->quantity);

        return response()->json([
            'status' => 'pending',
            'message' => 'Checkout stub — payment gateway not configured.',
            'order' => [
                'cartId' => $cart->id,
                'itemCount' => $cart->items->sum('quantity'),
                'subtotal' => $subtotal,
                'currency' => 'AED',
            ],
        ], 202);
    }
}
