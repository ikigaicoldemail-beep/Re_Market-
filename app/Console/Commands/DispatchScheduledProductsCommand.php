<?php

namespace App\Console\Commands;

use App\Events\ProductCreated;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DispatchScheduledProductsCommand extends Command
{
    protected $signature = 'products:dispatch-scheduled';

    protected $description = 'Publish scheduled products whose schedule_at time has arrived';

    public function handle(): int
    {
        if (! Schema::hasTable('products')) {
            $this->warn('Skipping scheduled product dispatch because the products table is not available yet.');

            return self::SUCCESS;
        }

        $due = Product::query()
            ->where('status', 'scheduled')
            ->whereNotNull('schedule_at')
            ->where('schedule_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($due as $product) {
            DB::transaction(function () use ($product, &$count) {
                $product->forceFill([
                    'status' => 'published',
                    'published_at' => now(),
                ])->save();
                $count++;
            });

            // Fire after commit so listeners (auto-post-to-social, notifications) see the persisted row.
            event(new ProductCreated($product->fresh()));
        }

        $this->info("Published {$count} scheduled product(s).");

        return self::SUCCESS;
    }
}
