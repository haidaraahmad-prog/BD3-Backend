@extends('admin.layout')

@section('title', 'Products')

@section('content')
    <header class="admin-page-header">
        <div>
            <p class="admin-kicker">Catalog</p>
            <h1>Products</h1>
            <p class="admin-muted">{{ $products->count() }} in catalog</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="admin-btn admin-btn--primary">Add product</a>
    </header>

    <section class="admin-panel">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Shape</th>
                    <th>Series</th>
                    <th>Price</th>
                    <th>New</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td class="admin-muted">{{ $product->slug }}</td>
                        <td>{{ ucfirst($product->shape) }}</td>
                        <td>{{ ucfirst($product->series) }}</td>
                        <td>${{ $product->price }}</td>
                        <td>{{ $product->is_new ? 'Yes' : '—' }}</td>
                        <td class="admin-actions">
                            <a href="{{ route('admin.products.edit', $product->slug) }}" class="admin-btn admin-btn--sm">Edit</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product->slug) }}" onsubmit="return confirm('Delete {{ $product->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn--sm admin-btn--danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
