<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Cart extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['id'];

    protected static function booted(): void
    {
        static::creating(function (Cart $cart) {
            if (! $cart->id) {
                $cart->id = (string) Str::uuid();
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
