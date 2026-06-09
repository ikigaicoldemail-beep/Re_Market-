<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('product_image_embeddings');
        Schema::dropIfExists('ai_search_logs');

        if (Schema::hasColumn('product_images', 'ai_embedding_status')) {
            Schema::table('product_images', function (Blueprint $table): void {
                $table->dropIndex(['ai_embedding_status']);
                $table->dropColumn('ai_embedding_status');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('product_images', 'ai_embedding_status')) {
            Schema::table('product_images', function (Blueprint $table): void {
                $table->string('ai_embedding_status')->default('pending');
                $table->index('ai_embedding_status');
            });
        }
    }
};
