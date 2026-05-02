<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->text('description')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('address_line')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_verified')->default(false);
            $table->unsignedInteger('followers_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('status');
            $table->index('is_verified');
            $table->index('city');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
