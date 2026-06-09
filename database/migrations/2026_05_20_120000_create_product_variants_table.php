<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('sku')->nullable();
            $table->json('attributes')->nullable();
            $table->unsignedBigInteger('price_amount');
            $table->unsignedBigInteger('original_price_amount')->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('product_id');
            $table->index('is_default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
