<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('disk')->default('product-images');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->string('ai_embedding_status')->default('pending');
            $table->timestamps();

            $table->index('product_id');
            $table->index('is_primary');
            $table->index('sort_order');
            $table->index('ai_embedding_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
