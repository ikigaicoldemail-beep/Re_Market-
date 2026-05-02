<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->string('status')->default('active')->after('password');
            $table->string('role')->default('user')->after('status');
            $table->timestamp('last_login_at')->nullable()->after('role');
            $table->rememberToken();
            $table->softDeletes();

            $table->unique('phone');
            $table->index('status');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['role']);
            $table->dropUnique(['phone']);

            $table->dropColumn([
                'email_verified_at',
                'phone_verified_at',
                'status',
                'role',
                'last_login_at',
                'remember_token',
                'deleted_at',
            ]);
        });
    }
};
