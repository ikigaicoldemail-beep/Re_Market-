<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_conditions', function (Blueprint $table) {
            $table->unsignedTinyInteger('quality_score')->default(50)->after('description');
            $table->string('color', 32)->nullable()->after('quality_score');
        });

        $now = now();
        $seed = [
            ['name' => 'Like New', 'slug' => 'like-new', 'rank' => 1, 'quality_score' => 100, 'color' => 'green',  'description' => 'Open box or barely used. No visible wear.'],
            ['name' => 'Excellent', 'slug' => 'excellent', 'rank' => 2, 'quality_score' => 90,  'color' => 'emerald', 'description' => 'Light cosmetic wear; works perfectly.'],
            ['name' => 'Good',      'slug' => 'good',      'rank' => 3, 'quality_score' => 80,  'color' => 'blue',    'description' => 'Visible wear from regular use; fully functional.'],
            ['name' => 'Fair',      'slug' => 'fair',      'rank' => 4, 'quality_score' => 70,  'color' => 'amber',   'description' => 'Noticeable wear; minor issues disclosed by seller.'],
            ['name' => 'Used',      'slug' => 'used',      'rank' => 5, 'quality_score' => 60,  'color' => 'orange',  'description' => 'Heavy use; functional but cosmetically worn.'],
            ['name' => 'For Parts', 'slug' => 'for-parts', 'rank' => 6, 'quality_score' => 40,  'color' => 'red',     'description' => 'Not fully working; suitable for parts or repair.'],
        ];

        foreach ($seed as $row) {
            $existing = DB::table('product_conditions')->where('slug', $row['slug'])->first();
            $row['updated_at'] = $now;
            if ($existing) {
                DB::table('product_conditions')->where('id', $existing->id)->update($row);
            } else {
                $row['created_at'] = $now;
                DB::table('product_conditions')->insert($row);
            }
        }
    }

    public function down(): void
    {
        Schema::table('product_conditions', function (Blueprint $table) {
            $table->dropColumn(['quality_score', 'color']);
        });
    }
};
