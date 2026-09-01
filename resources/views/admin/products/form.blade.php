@extends('admin.layout')

@section('title', $product ? 'Edit product' : 'New product')

@section('content')
    <header class="admin-page-header">
        <div>
            <a href="{{ route('admin.products.index') }}" class="admin-link">← Products</a>
            <h1>{{ $product ? 'Edit '.$product->name : 'New product' }}</h1>
        </div>
    </header>

    <section class="admin-panel">
        <form method="POST" action="{{ $product ? route('admin.products.update', $product->slug) : route('admin.products.store') }}" class="admin-form admin-form--grid">
            @csrf
            @if ($product)
                @method('PUT')
            @endif

            <label>
                <span>Slug</span>
                <input type="text" name="slug" value="{{ old('slug', $product?->slug) }}" required pattern="[a-z0-9-]+">
            </label>
            <label>
                <span>Name</span>
                <input type="text" name="name" value="{{ old('name', $product?->name) }}" required>
            </label>
            <label>
                <span>Price (AED)</span>
                <input type="number" name="price" value="{{ old('price', $product?->price) }}" required min="0">
            </label>
            <label>
                <span>Release date</span>
                <input type="date" name="released_at" value="{{ old('released_at', $product?->released_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </label>
            <label>
                <span>Shape</span>
                <select name="shape" required>
                    @foreach (['aviator', 'geometric', 'shield'] as $shape)
                        <option value="{{ $shape }}" @selected(old('shape', $product?->shape) === $shape)>{{ ucfirst($shape) }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Series</span>
                <select name="series" required>
                    @foreach (['axiom', 'vector', 'apex'] as $series)
                        <option value="{{ $series }}" @selected(old('series', $product?->series) === $series)>{{ ucfirst($series) }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Lens</span>
                <select name="lens" required>
                    @foreach (['polarized', 'gradient', 'mirror'] as $lens)
                        <option value="{{ $lens }}" @selected(old('lens', $product?->lens) === $lens)>{{ ucfirst($lens) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="admin-form-span-2">
                <span>Hero image path</span>
                <input type="text" name="image" value="{{ old('image', $product?->image ?? '/images/products/swatch-aviator-black.webp') }}" required>
            </label>
            <label>
                <span>Rating</span>
                <input type="number" name="rating" value="{{ old('rating', $product?->rating ?? 4.8) }}" min="0" max="5" step="0.1">
            </label>
            <label>
                <span>Reviews</span>
                <input type="number" name="reviews_count" value="{{ old('reviews_count', $product?->reviews_count ?? 0) }}" min="0">
            </label>

            <label class="admin-checkbox admin-form-span-2">
                <input type="checkbox" name="is_new" value="1" @checked(old('is_new', $product?->is_new))>
                <span>Mark as new arrival</span>
            </label>

            <fieldset class="admin-form-span-2">
                <legend>Colors</legend>
                <div class="admin-color-grid">
                    @php
                        $selected = old('color_ids', $product?->colors->pluck('id')->all() ?? ['black']);
                    @endphp
                    @foreach ($colors as $color)
                        <label class="admin-color-chip">
                            <input type="checkbox" name="color_ids[]" value="{{ $color->id }}" @checked(in_array($color->id, $selected, true))>
                            <span style="--chip: {{ $color->hex }}">{{ $color->label }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            @if ($errors->any())
                <div class="admin-alert admin-alert--error admin-form-span-2">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="admin-form-span-2">
                <button type="submit" class="admin-btn admin-btn--primary">
                    {{ $product ? 'Save changes' : 'Create product' }}
                </button>
            </div>
        </form>
    </section>
@endsection
