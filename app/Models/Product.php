<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'slug';

    protected $keyType = 'string';

    protected $fillable = [
        'slug',
        'name',
        'price',
        'shape',
        'series',
        'lens',
        'image',
        'rating',
        'reviews_count',
        'is_new',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'rating' => 'float',
            'reviews_count' => 'integer',
            'is_new' => 'boolean',
            'released_at' => 'date',
        ];
    }

    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(Color::class, 'product_color', 'product_slug', 'color_id')
            ->withPivot(['swatch_image', 'sort_order'])
            ->orderByPivot('sort_order');
    }

    /** @param Builder<Product> $query */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if (! empty($filters['shape'])) {
            $shapes = is_array($filters['shape']) ? $filters['shape'] : explode(',', $filters['shape']);
            $query->whereIn('shape', $shapes);
        }

        if (! empty($filters['series'])) {
            $series = is_array($filters['series']) ? $filters['series'] : explode(',', $filters['series']);
            $query->whereIn('series', $series);
        }

        if (! empty($filters['lens'])) {
            $lenses = is_array($filters['lens']) ? $filters['lens'] : explode(',', $filters['lens']);
            $query->whereIn('lens', $lenses);
        }

        if (isset($filters['min']) && $filters['min'] !== '') {
            $query->where('price', '>=', (int) $filters['min']);
        }

        if (isset($filters['max']) && $filters['max'] !== '') {
            $query->where('price', '<=', (int) $filters['max']);
        }

        return $query;
    }

    /** @param Builder<Product> $query */
    public function scopeSort(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'price-asc' => $query->orderBy('price'),
            'price-desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('released_at'),
        };
    }
}
