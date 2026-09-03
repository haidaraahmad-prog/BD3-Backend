<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CheckoutComService;
use App\Support\CartResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private CartResolver $cartResolver,
        private CheckoutComService $checkout,
    ) {}

    public function pay(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cartId' => ['nullable', 'uuid', 'exists:carts,id'],
            'email' => ['nullable', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:120'],
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

        $cart->load(['items.product', 'items.color']);

        if ($cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty.'], 422);
        }

        if (! $this->checkout->isConfigured()) {
            return response()->json([
                'message' => 'Payment gateway not configured. Add CHECKOUT_SECRET_KEY to .env (use sandbox keys for testing).',
            ], 503);
        }

        $subtotal = $cart->items->sum(fn ($item) => $item->product->price * $item->quantity);
        $reference = 'BD3-'.strtoupper(Str::random(10));

        $order = Order::query()->create([
            'reference' => $reference,
            'cart_id' => $cart->id,
            'user_id' => $user?->id,
            'subtotal' => $subtotal,
            'currency' => config('checkout.currency', 'AED'),
            'status' => 'pending',
        ]);

        foreach ($cart->items as $item) {
            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_slug' => $item->product_slug,
                'product_name' => $item->product->name,
                'color_id' => $item->color_id,
                'color_label' => $item->color->label ?? $item->color_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->product->price,
            ]);
        }

        $email = $validated['email'] ?? $user?->email ?? 'guest@bd3.local';
        $name = $validated['name'] ?? $user?->name ?? 'BD3 Guest';

        try {
            $session = $this->checkout->createHostedPayment($order, $email, $name);
        } catch (RuntimeException $e) {
            $order->update(['status' => 'failed']);

            return response()->json(['message' => $e->getMessage()], 502);
        }

        $order->update(['checkout_session_id' => $session['sessionId']]);

        return response()->json([
            'checkoutUrl' => $session['checkoutUrl'],
            'order' => $this->orderPayload($order->fresh('items')),
        ], 201);
    }

    public function show(string $reference): JsonResponse
    {
        $order = Order::query()->with('items')->where('reference', $reference)->firstOrFail();

        return response()->json(['order' => $this->orderPayload($order)]);
    }

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Cko-Signature');

        if (! $this->checkout->verifyWebhookSignature($payload, $signature)) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $event = json_decode($payload, true);
        if (! is_array($event)) {
            return response()->json(['message' => 'Invalid payload.'], 400);
        }

        $type = $event['type'] ?? '';
        $data = $event['data'] ?? [];
        $reference = $data['reference'] ?? null;

        if (! $reference) {
            return response()->json(['received' => true]);
        }

        $order = Order::query()->where('reference', $reference)->first();
        if (! $order) {
            return response()->json(['received' => true]);
        }

        if (in_array($type, ['payment_approved', 'payment_captured'], true)) {
            $order->markPaid($data['id'] ?? null);
            $order->cart?->items()->delete();
        } elseif (in_array($type, ['payment_declined', 'payment_voided', 'payment_expired'], true)) {
            $order->update(['status' => 'failed']);
        }

        return response()->json(['received' => true]);
    }

    /** @return array<string, mixed> */
    private function orderPayload(Order $order): array
    {
        return [
            'id' => $order->id,
            'reference' => $order->reference,
            'status' => $order->status,
            'subtotal' => $order->subtotal,
            'currency' => $order->currency,
            'itemCount' => $order->items->sum('quantity'),
            'items' => $order->items->map(fn (OrderItem $item) => [
                'productName' => $item->product_name,
                'colorLabel' => $item->color_label,
                'quantity' => $item->quantity,
                'unitPrice' => $item->unit_price,
                'lineTotal' => $item->unit_price * $item->quantity,
            ])->values(),
            'paidAt' => $order->paid_at?->toIso8601String(),
        ];
    }
}
