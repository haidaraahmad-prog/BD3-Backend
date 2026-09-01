<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->slug,
            'name' => $this->name,
            'price' => $this->price,
            'shape' => $this->shape,
            'series' => $this->series,
            'lens' => $this->lens,
            'image' => $this->image,
            'colors' => $this->whenLoaded('colors', fn () => $this->colors->map(fn ($color) => [
                'id' => $color->id,
                'label' => $color->label,
                'hex' => $color->hex,
                'image' => $color->pivot->swatch_image,
            ])),
            'rating' => $this->rating,
            'reviews' => $this->reviews_count,
            'isNew' => $this->is_new,
            'createdAt' => $this->released_at->format('Y-m-d'),
        ];
    }
}
