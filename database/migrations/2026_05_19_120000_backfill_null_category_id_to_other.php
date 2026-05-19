<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $otherId = DB::table('categories')->where('slug', 'other')->value('id');

        if (! $otherId) {
            $otherId = DB::table('categories')->insertGetId([
                'parent_id' => null,
                'name' => 'Other',
                'slug' => 'other',
                'description' => 'Items that don\'t fit any other category.',
                'status' => 'active',
                'sort_order' => 99,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('products')
            ->whereNull('category_id')
            ->update(['category_id' => $otherId, 'updated_at' => $now]);
    }

    public function down(): void
    {
        // Intentionally non-reversible: reverting the backfill would require
        // tracking which rows were touched, and the "Other" category may be
        // legitimately in use by then.
    }
};
