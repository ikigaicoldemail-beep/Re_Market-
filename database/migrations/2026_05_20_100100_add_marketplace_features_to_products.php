<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained('brands')->nullOnDelete();
            $table->unsignedBigInteger('original_price_amount')->nullable()->after('price_amount');
            $table->boolean('is_featured')->default(false)->after('allow_offers');
            $table->timestamp('featured_at')->nullable()->after('is_featured');
            $table->json('specs')->nullable()->after('description');

            $table->index('brand_id');
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['brand_id']);
            $table->dropIndex(['is_featured']);
            $table->dropConstrainedForeignId('brand_id');
            $table->dropColumn(['original_price_amount', 'is_featured', 'featured_at', 'specs']);
        });
    }
};
