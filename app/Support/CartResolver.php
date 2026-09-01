<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;

class CartResolver
{
    public function resolveForStore(Request $request, ?string $cartId = null): Cart
    {
        $user = $request->user();

        if ($user instanceof User) {
            return Cart::query()->firstOrCreate(['user_id' => $user->id]);
        }

        if ($cartId) {
            return Cart::query()->findOrFail($cartId);
        }

        return Cart::query()->create([]);
    }

    public function resolveForUser(User $user): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => $user->id]);
    }

    public function canAccess(Request $request, Cart $cart): bool
    {
        if ($cart->user_id === null) {
            return true;
        }

        $user = $request->user();

        return $user instanceof User && $user->id === $cart->user_id;
    }
}
