<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;

class CartResolver
{
    public function resolveForUser(User $user): Cart
    {
        return Cart::query()->firstOrCreate(['user_id' => $user->id]);
    }

    public function canAccess(Request $request, Cart $cart): bool
    {
        $user = $request->user();

        return $user instanceof User && $cart->user_id !== null && $user->id === $cart->user_id;
    }
}
