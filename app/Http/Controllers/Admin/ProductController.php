<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Models\Product;
use App\Support\ProductWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->with('colors')
            ->orderByDesc('released_at')
            ->get();

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        $colors = Color::query()->orderBy('label')->get();

        return view('admin.products.form', [
            'product' => null,
            'colors' => $colors,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = ProductWriter::validateWeb($request->all());
        $validated['is_new'] = $request->boolean('is_new');

        ProductWriter::create($validated);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created.');
    }

    public function edit(string $slug): View
    {
        $product = Product::query()->with('colors')->findOrFail($slug);
        $colors = Color::query()->orderBy('label')->get();

        return view('admin.products.form', compact('product', 'colors'));
    }

    public function update(Request $request, string $slug): RedirectResponse
    {
        $product = Product::query()->findOrFail($slug);
        $validated = ProductWriter::validateWeb($request->all(), $slug);
        $validated['is_new'] = $request->boolean('is_new');

        ProductWriter::update($product, $validated);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated.');
    }

    public function destroy(string $slug): RedirectResponse
    {
        Product::query()->findOrFail($slug)->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted.');
    }
}
