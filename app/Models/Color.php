<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Color extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = ['id', 'label', 'hex'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_color', 'color_id', 'product_slug')
            ->withPivot(['swatch_image', 'sort_order'])
            ->orderByPivot('sort_order');
    }
}
