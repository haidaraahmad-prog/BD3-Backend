<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Favourite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavouriteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $favourites = $request->user()
            ->favourites()
            ->with(['product.colors'])
            ->latest()
            ->get();

        return response()->json([
            'data' => ProductResource::collection($favourites->pluck('product')),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'productId' => ['required', 'string', 'exists:products,slug'],
        ]);

        Favourite::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'product_slug' => $validated['productId'],
        ]);

        return response()->json(['message' => 'Added to favourites.'], 201);
    }

    public function destroy(Request $request, string $productSlug): JsonResponse
    {
        $deleted = Favourite::query()
            ->where('user_id', $request->user()->id)
            ->where('product_slug', $productSlug)
            ->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Favourite not found.'], 404);
        }

        return response()->json(['message' => 'Removed from favourites.']);
    }
}
