<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('product_title');
            $table->string('product_slug')->nullable();
            $table->string('product_image_path')->nullable();
            $table->string('product_condition_label')->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_amount');
            $table->unsignedBigInteger('line_total_amount');
            $table->string('fulfillment_status')->default('pending');
            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
            $table->index('seller_id');
            $table->index('fulfillment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
