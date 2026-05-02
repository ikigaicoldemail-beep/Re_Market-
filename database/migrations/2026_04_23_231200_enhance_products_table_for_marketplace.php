<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->after('store_id')->constrained()->nullOnDelete();
            $table->foreignId('product_condition_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            $table->string('slug')->nullable()->after('title');
            $table->string('sku')->nullable()->after('slug');
            $table->unsignedBigInteger('price_amount')->default(0)->after('price');
            $table->char('currency', 3)->default('USD')->after('price_amount');
            $table->unsignedInteger('stock_quantity')->default(1)->after('currency');
            $table->string('location_country_code', 2)->nullable()->after('location');
            $table->string('location_state')->nullable()->after('location_country_code');
            $table->string('location_city')->nullable()->after('location_state');
            $table->string('status')->default('draft')->after('location_city');
            $table->string('moderation_status')->default('approved')->after('status');
            $table->string('visibility')->default('public')->after('moderation_status');
            $table->boolean('allow_offers')->default(true)->after('visibility');
            $table->timestamp('published_at')->nullable()->after('allow_offers');
            $table->softDeletes();

            $table->unique('slug');
            $table->unique('sku');
            $table->index('store_id');
            $table->index('category_id');
            $table->index('product_condition_id');
            $table->index('status');
            $table->index('moderation_status');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['store_id']);
            $table->dropIndex(['category_id']);
            $table->dropIndex(['product_condition_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['moderation_status']);
            $table->dropIndex(['published_at']);
            $table->dropUnique(['slug']);
            $table->dropUnique(['sku']);

            $table->dropConstrainedForeignId('store_id');
            $table->dropConstrainedForeignId('category_id');
            $table->dropConstrainedForeignId('product_condition_id');
            $table->dropColumn([
                'slug',
                'sku',
                'price_amount',
                'currency',
                'stock_quantity',
                'location_country_code',
                'location_state',
                'location_city',
                'status',
                'moderation_status',
                'visibility',
                'allow_offers',
                'published_at',
                'deleted_at',
            ]);
        });
    }
};
