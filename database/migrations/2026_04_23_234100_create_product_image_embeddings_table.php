<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_image_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_image_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('embedding_model')->nullable();
            $table->json('embedding_vector')->nullable();
            $table->string('vector_hash')->nullable();
            $table->string('status')->default('completed');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index('product_image_id');
            $table->index('provider');
            $table->index('status');
            $table->index('vector_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_image_embeddings');
    }
};
