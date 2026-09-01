@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <header class="admin-page-header">
        <div>
            <p class="admin-kicker">Control panel</p>
            <h1>Dashboard</h1>
        </div>
        <a href="{{ config('app.frontend_url', 'http://localhost:5173') }}" class="admin-link" target="_blank" rel="noopener">View storefront →</a>
    </header>

    <div class="admin-stats">
        <div class="admin-stat-card">
            <span>Products</span>
            <strong>{{ $stats['products'] }}</strong>
        </div>
        <div class="admin-stat-card">
            <span>New arrivals</span>
            <strong>{{ $stats['newProducts'] }}</strong>
        </div>
        <div class="admin-stat-card">
            <span>Colors</span>
            <strong>{{ $stats['colors'] }}</strong>
        </div>
        <div class="admin-stat-card">
            <span>Active carts</span>
            <strong>{{ $stats['carts'] }}</strong>
        </div>
        <div class="admin-stat-card">
            <span>Cart items</span>
            <strong>{{ $stats['cartItems'] }}</strong>
        </div>
    </div>

    <section class="admin-panel">
        <div class="admin-panel-header">
            <h2>Recent products</h2>
            <a href="{{ route('admin.products.index') }}" class="admin-link">Manage all →</a>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Price</th>
                    <th>Released</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentProducts as $product)
                    <tr>
                        <td>
                            <a href="{{ route('admin.products.edit', $product->slug) }}">{{ $product->name }}</a>
                        </td>
                        <td class="admin-muted">{{ $product->slug }}</td>
                        <td>${{ $product->price }}</td>
                        <td class="admin-muted">{{ $product->released_at->format('Y-m-d') }}</td>
                        <td>
                            @if ($product->is_new)
                                <span class="admin-badge">New</span>
                            @else
                                <span class="admin-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="admin-muted">No products yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
