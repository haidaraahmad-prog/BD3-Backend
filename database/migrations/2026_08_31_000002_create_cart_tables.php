<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('cart_id');
            $table->string('product_slug');
            $table->string('color_id');
            $table->unsignedTinyInteger('quantity')->default(1);
            $table->timestamps();

            $table->foreign('cart_id')->references('id')->on('carts')->cascadeOnDelete();
            $table->foreign('product_slug')->references('slug')->on('products')->cascadeOnDelete();
            $table->foreign('color_id')->references('id')->on('colors');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
    }
};
