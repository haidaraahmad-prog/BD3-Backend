<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Support\CartResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private CartResolver $cartResolver) {}

    public function pay(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cartId' => ['nullable', 'uuid', 'exists:carts,id'],
        ]);

        $user = $request->user();

        if ($user) {
            $cart = $this->cartResolver->resolveForUser($user);
        } elseif (! empty($validated['cartId'])) {
            $cart = Cart::query()->findOrFail($validated['cartId']);
        } else {
            return response()->json(['message' => 'Cart ID is required.'], 422);
        }

        if (! $this->cartResolver->canAccess($request, $cart)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $cart->load(['items.product']);

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
