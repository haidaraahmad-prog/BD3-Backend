<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ColorController extends Controller
{
    public function index(): View
    {
        $colors = Color::query()->orderBy('label')->get();

        return view('admin.colors.index', compact('colors'));
    }

    public function create(): View
    {
        return view('admin.colors.form', ['color' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9-]+$/', 'unique:colors,id'],
            'label' => ['required', 'string', 'max:100'],
            'hex' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        Color::query()->create($validated);

        return redirect()
            ->route('admin.colors.index')
            ->with('success', 'Color created.');
    }

    public function edit(string $id): View
    {
        $color = Color::query()->findOrFail($id);

        return view('admin.colors.form', compact('color'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $color = Color::query()->findOrFail($id);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'hex' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $color->update($validated);

        return redirect()
            ->route('admin.colors.index')
            ->with('success', 'Color updated.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $color = Color::query()->findOrFail($id);

        if ($color->products()->exists()) {
            return back()->with('error', 'Color is attached to products.');
        }

        $color->delete();

        return redirect()
            ->route('admin.colors.index')
            ->with('success', 'Color deleted.');
    }
}
