<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ColorController extends Controller
{
    public function index(): JsonResponse
    {
        $colors = Color::query()->orderBy('id')->get();

        return response()->json([
            'data' => $colors->map(fn (Color $c) => [
                'id' => $c->id,
                'label' => $c->label,
                'hex' => $c->hex,
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9-]+$/', 'unique:colors,id'],
            'label' => ['required', 'string', 'max:100'],
            'hex' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $color = Color::query()->create($validated);

        return response()->json([
            'id' => $color->id,
            'label' => $color->label,
            'hex' => $color->hex,
        ], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $color = Color::query()->find($id);

        if (! $color) {
            return response()->json(['message' => 'Color not found.'], 404);
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'hex' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $color->update($validated);

        return response()->json([
            'id' => $color->id,
            'label' => $color->label,
            'hex' => $color->hex,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $color = Color::query()->find($id);

        if (! $color) {
            return response()->json(['message' => 'Color not found.'], 404);
        }

        if ($color->products()->exists()) {
            return response()->json(['message' => 'Color is attached to products.'], 422);
        }

        $color->delete();

        return response()->json(['message' => 'Color deleted.']);
    }
}
