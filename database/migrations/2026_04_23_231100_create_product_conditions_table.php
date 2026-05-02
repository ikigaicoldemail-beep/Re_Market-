<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('rank')->default(1);
            $table->timestamps();

            $table->index('rank');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_conditions');
    }
};
