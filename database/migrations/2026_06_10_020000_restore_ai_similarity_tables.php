<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_search_logs')) {
            Schema::create('ai_search_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('product_image_id')->nullable()->constrained()->nullOnDelete();
                $table->string('query_image_path')->nullable();
                $table->string('provider')->nullable();
                $table->string('status')->default('pending');
                $table->string('embedding_version')->nullable();
                $table->unsignedInteger('top_k')->default(10);
                $table->unsignedInteger('result_count')->default(0);
                $table->json('request_payload')->nullable();
                $table->json('response_payload')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('searched_at')->nullable();
                $table->timestamps();

                $table->index('user_id');
                $table->index('product_id');
                $table->index('product_image_id');
                $table->index('provider');
                $table->index('status');
                $table->index('searched_at');
            });
        }

        if (! Schema::hasTable('product_image_embeddings')) {
            Schema::create('product_image_embeddings', function (Blueprint $table): void {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('product_image_embeddings');
        Schema::dropIfExists('ai_search_logs');
    }
};
