<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{slug}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{slug}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{slug}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::get('colors', [ColorController::class, 'index'])->name('colors.index');
        Route::get('colors/create', [ColorController::class, 'create'])->name('colors.create');
        Route::post('colors', [ColorController::class, 'store'])->name('colors.store');
        Route::get('colors/{id}/edit', [ColorController::class, 'edit'])->name('colors.edit');
        Route::put('colors/{id}', [ColorController::class, 'update'])->name('colors.update');
        Route::delete('colors/{id}', [ColorController::class, 'destroy'])->name('colors.destroy');
    });
});
