@extends('admin.layout')

@section('title', $color ? 'Edit color' : 'New color')

@section('content')
    <header class="admin-page-header">
        <div>
            <a href="{{ route('admin.colors.index') }}" class="admin-link">← Colors</a>
            <h1>{{ $color ? 'Edit '.$color->label : 'New color' }}</h1>
        </div>
    </header>

    <section class="admin-panel">
        <form method="POST" action="{{ $color ? route('admin.colors.update', $color->id) : route('admin.colors.store') }}" class="admin-form">
            @csrf
            @if ($color)
                @method('PUT')
            @endif

            @unless ($color)
                <label>
                    <span>ID</span>
                    <input type="text" name="id" value="{{ old('id') }}" required pattern="[a-z0-9-]+">
                </label>
            @endunless

            <label>
                <span>Label</span>
                <input type="text" name="label" value="{{ old('label', $color?->label) }}" required>
            </label>
            <label>
                <span>Hex</span>
                <input type="text" name="hex" value="{{ old('hex', $color?->hex ?? '#1A1A1A') }}" required pattern="#[0-9A-Fa-f]{6}">
            </label>

            @if ($errors->any())
                <div class="admin-alert admin-alert--error">
                    {{ $errors->first() }}
                </div>
            @endif

            <button type="submit" class="admin-btn admin-btn--primary">
                {{ $color ? 'Save changes' : 'Create color' }}
            </button>
        </form>
    </section>
@endsection
