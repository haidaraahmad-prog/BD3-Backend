<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CheckoutComService
{
    public function isConfigured(): bool
    {
        return filled(config('checkout.secret_key'));
    }

    /**
     * Create a hosted payment page session (Checkout.com sandbox/live).
     *
     * @return array{sessionId: string, checkoutUrl: string}
     */
    public function createHostedPayment(Order $order, string $customerEmail, string $customerName): array
    {
        $secret = config('checkout.secret_key');
        if (! $secret) {
            throw new RuntimeException('Checkout.com is not configured. Set CHECKOUT_SECRET_KEY in .env.');
        }

        $frontend = rtrim(config('app.frontend_url'), '/');
        $amount = $order->subtotal * 100; // AED fils

        $payload = [
            'amount' => $amount,
            'currency' => config('checkout.currency', 'AED'),
            'reference' => $order->reference,
            'description' => 'BD3 Eyewear order '.$order->reference,
            'billing' => [
                'address' => [
                    'country' => 'AE',
                ],
            ],
            'customer' => [
                'email' => $customerEmail,
                'name' => $customerName,
            ],
            'success_url' => $frontend.'/checkout/success?reference='.urlencode($order->reference),
            'cancel_url' => $frontend.'/checkout/cancel?reference='.urlencode($order->reference),
            'failure_url' => $frontend.'/checkout/cancel?reference='.urlencode($order->reference),
        ];

        if ($channel = config('checkout.processing_channel_id')) {
            $payload['processing_channel_id'] = $channel;
        }

        try {
            $response = Http::withToken($secret)
                ->acceptJson()
                ->post(config('checkout.api_url').'/hosted-payments', $payload)
                ->throw();
        } catch (RequestException $e) {
            $body = $e->response?->json();
            $message = is_array($body) ? ($body['error_type'] ?? $body['message'] ?? $e->getMessage()) : $e->getMessage();
            throw new RuntimeException('Checkout.com: '.$message, 0, $e);
        }

        $data = $response->json();
        $checkoutUrl = $data['_links']['redirect']['href'] ?? null;
        $sessionId = $data['id'] ?? null;

        if (! $checkoutUrl || ! $sessionId) {
            throw new RuntimeException('Checkout.com returned an invalid hosted payment response.');
        }

        return [
            'sessionId' => $sessionId,
            'checkoutUrl' => $checkoutUrl,
        ];
    }

    public function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        $secret = config('checkout.webhook_secret');
        if (! $secret || ! $signature) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signature);
    }
}
