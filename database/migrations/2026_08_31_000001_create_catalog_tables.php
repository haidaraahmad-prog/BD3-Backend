<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('colors', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('label');
            $table->string('hex', 7);
        });

        Schema::create('products', function (Blueprint $table) {
            $table->string('slug')->primary();
            $table->string('name');
            $table->unsignedInteger('price');
            $table->string('shape'); // aviator | geometric | shield
            $table->string('series'); // axiom | vector | apex
            $table->string('lens'); // polarized | gradient | mirror
            $table->string('image');
            $table->decimal('rating', 2, 1)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->boolean('is_new')->default(false);
            $table->date('released_at');
            $table->timestamps();

            $table->index(['shape', 'series', 'lens']);
            $table->index('released_at');
            $table->index('price');
        });

        Schema::create('product_color', function (Blueprint $table) {
            $table->id();
            $table->string('product_slug');
            $table->string('color_id');
            $table->string('swatch_image');
            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->foreign('product_slug')->references('slug')->on('products')->cascadeOnDelete();
            $table->foreign('color_id')->references('id')->on('colors')->cascadeOnDelete();
            $table->unique(['product_slug', 'color_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_color');
        Schema::dropIfExists('products');
        Schema::dropIfExists('colors');
    }
};
