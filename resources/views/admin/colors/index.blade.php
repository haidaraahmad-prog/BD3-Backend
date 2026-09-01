@extends('admin.layout')

@section('title', 'Colors')

@section('content')
    <header class="admin-page-header">
        <div>
            <p class="admin-kicker">Catalog</p>
            <h1>Colors</h1>
        </div>
        <a href="{{ route('admin.colors.create') }}" class="admin-btn admin-btn--primary">Add color</a>
    </header>

    <section class="admin-panel">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Swatch</th>
                    <th>ID</th>
                    <th>Label</th>
                    <th>Hex</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($colors as $color)
                    <tr>
                        <td><span class="admin-swatch" style="background: {{ $color->hex }}"></span></td>
                        <td class="admin-muted">{{ $color->id }}</td>
                        <td>{{ $color->label }}</td>
                        <td>{{ $color->hex }}</td>
                        <td class="admin-actions">
                            <a href="{{ route('admin.colors.edit', $color->id) }}" class="admin-btn admin-btn--sm">Edit</a>
                            <form method="POST" action="{{ route('admin.colors.destroy', $color->id) }}" onsubmit="return confirm('Delete {{ $color->label }}?')">
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
