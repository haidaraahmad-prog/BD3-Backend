<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — BD3 Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="admin-login-page">
    <div class="admin-login-card">
        <p class="admin-kicker">BD3 Admin</p>
        <h1>Sign in</h1>
        <p class="admin-muted">Manage catalog, colors, and store metrics.</p>

        @if ($errors->any())
            <div class="admin-alert admin-alert--error">
                {{ $errors->first() }}
            </div>
        @endif

        @if (session('error'))
            <div class="admin-alert admin-alert--error">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}" class="admin-form">
            @csrf
            <label>
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email', 'admin@bd3.ae') }}" required autofocus>
            </label>
            <label>
                <span>Password</span>
                <input type="password" name="password" required>
            </label>
            <label class="admin-checkbox">
                <input type="checkbox" name="remember" value="1">
                <span>Remember me</span>
            </label>
            <button type="submit" class="admin-btn admin-btn--primary">Sign in</button>
        </form>
    </div>
</body>
</html>
