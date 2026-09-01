<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — BD3</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
    <div class="admin-shell">
        @auth
            <aside class="admin-sidebar">
                <a href="{{ route('admin.dashboard') }}" class="admin-brand">BD3 Admin</a>
                <p class="admin-user">{{ auth()->user()->email }}</p>
                <nav class="admin-nav">
                    <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.dashboard')])>Dashboard</a>
                    <a href="{{ route('admin.products.index') }}" @class(['active' => request()->routeIs('admin.products.*')])>Products</a>
                    <a href="{{ route('admin.colors.index') }}" @class(['active' => request()->routeIs('admin.colors.*')])>Colors</a>
                </nav>
                <form method="POST" action="{{ route('admin.logout') }}" class="admin-logout">
                    @csrf
                    <button type="submit">Sign out</button>
                </form>
            </aside>
        @endauth

        <main class="admin-main">
            @if (session('success'))
                <div class="admin-alert admin-alert--success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="admin-alert admin-alert--error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
