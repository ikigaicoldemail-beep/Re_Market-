<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('username')->nullable()->unique();
            $table->string('avatar_path')->nullable();
            $table->string('cover_path')->nullable();
            $table->text('bio')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->foreignId('default_store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->boolean('is_seller')->default(false);
            $table->string('profile_visibility')->default('public');
            $table->timestamps();

            $table->unique('user_id');
            $table->index('is_seller');
            $table->index('profile_visibility');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
