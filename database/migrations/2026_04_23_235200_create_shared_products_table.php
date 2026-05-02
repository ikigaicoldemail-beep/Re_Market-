<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shared_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('platform')->nullable();
            $table->string('destination')->nullable();
            $table->string('status')->default('shared');
            $table->json('metadata')->nullable();
            $table->timestamp('shared_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('product_id');
            $table->index('platform');
            $table->index('shared_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shared_products');
    }
};
