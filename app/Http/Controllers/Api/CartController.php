<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Support\CartResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartResolver $cartResolver) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'productId' => ['required', 'string', 'exists:products,slug'],
            'colorId' => ['required', 'string', 'exists:colors,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9'],
        ]);

        $product = Product::query()->with('colors')->findOrFail($validated['productId']);

        if (! $product->colors->contains('id', $validated['colorId'])) {
            return response()->json(['message' => 'Color not available for this product.'], 422);
        }

        $cart = $this->cartResolver->resolveForUser($request->user());

        $item = CartItem::query()->updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_slug' => $product->slug,
                'color_id' => $validated['colorId'],
            ],
            ['quantity' => $validated['quantity']],
        );

        $cart->load(['items.product', 'items.color']);

        return response()->json([
            'cartId' => $cart->id,
            'itemCount' => $cart->items->sum('quantity'),
            'item' => [
                'id' => $item->id,
                'productId' => $item->product_slug,
                'colorId' => $item->color_id,
                'quantity' => $item->quantity,
            ],
            'cart' => $this->formatCart($cart),
        ], 201);
    }

    public function mine(Request $request): JsonResponse
    {
        $cart = $this->cartResolver->resolveForUser($request->user());
        $cart->load(['items.product', 'items.color']);

        return response()->json($this->formatCart($cart));
    }

    public function destroy(Request $request, int $itemId): JsonResponse
    {
        $cart = $this->cartResolver->resolveForUser($request->user());

        $item = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->first();

        if (! $item) {
            return response()->json(['message' => 'Cart item not found.'], 404);
        }

        $item->delete();

        $cart->load(['items.product', 'items.color']);

        return response()->json($this->formatCart($cart));
    }

    /** @return array<string, mixed> */
    private function formatCart(Cart $cart): array
    {
        return [
            'id' => $cart->id,
            'itemCount' => $cart->items->sum('quantity'),
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
        ];
    }
}
