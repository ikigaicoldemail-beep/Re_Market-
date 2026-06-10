<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_visual_index', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('listing_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_image_id')->nullable()->constrained('product_images')->nullOnDelete();
            $table->unsignedBigInteger('faiss_id')->unique();
            $table->timestamps();

            $table->index('listing_id');
            $table->index('product_image_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_visual_index');
    }
};
