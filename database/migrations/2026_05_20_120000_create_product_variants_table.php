<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('sku')->nullable();
            $table->json('attributes')->nullable();
            $table->unsignedBigInteger('price_amount');
            $table->unsignedBigInteger('original_price_amount')->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index('product_id');
            $table->index('is_default');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
        });

        // MySQL refuses to drop the (cart_id, product_id) unique while the cart_id FK uses it as the
        // backing index. Add a dedicated cart_id index first, then drop the composite unique, then add
        // the new variant-aware unique. The product_id FK is already backed by cart_items_product_id_index.
        Schema::table('cart_items', function (Blueprint $table) {
            $table->index(['cart_id'], 'cart_items_cart_id_idx');
        });
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_items_cart_id_product_id_unique');
        });
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(['cart_id', 'product_id', 'product_variant_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->after('product_id')->constrained('product_variants')->nullOnDelete();
            $table->string('variant_label')->nullable()->after('product_title');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_variant_id');
            $table->dropColumn('variant_label');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique(['cart_id', 'product_id', 'product_variant_id']);
            $table->dropConstrainedForeignId('product_variant_id');
            $table->dropIndex('cart_items_cart_id_idx');
            $table->unique(['cart_id', 'product_id']);
        });

        Schema::dropIfExists('product_variants');
    }
};
