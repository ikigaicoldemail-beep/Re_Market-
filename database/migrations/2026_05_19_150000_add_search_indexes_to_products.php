<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE products ADD FULLTEXT INDEX products_title_description_ft (title, description)');
        }

        Schema::table('products', function (Blueprint $table) {
            // Default public listing: status='published' AND visibility='public' ORDER BY created_at/published_at DESC
            $table->index(['status', 'visibility', 'published_at'], 'products_status_visibility_pub_idx');
            // Category filter narrowed by status
            $table->index(['category_id', 'status'], 'products_cat_status_idx');
            // Province/city filter narrowed by status
            $table->index(['location_city', 'status'], 'products_city_status_idx');
            // Store page (already has store_id index, but composite with status keeps the published filter cheap)
            $table->index(['store_id', 'status'], 'products_store_status_idx');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE products DROP INDEX products_title_description_ft');
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_visibility_pub_idx');
            $table->dropIndex('products_cat_status_idx');
            $table->dropIndex('products_city_status_idx');
            $table->dropIndex('products_store_status_idx');
        });
    }
};
