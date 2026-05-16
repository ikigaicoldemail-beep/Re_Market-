<?php

namespace App\Console\Commands;

use App\Jobs\GenerateProductImageEmbeddingJob;
use App\Models\ProductImage;
use Illuminate\Console\Command;

class ReembedProductImagesCommand extends Command
{
    protected $signature = 'ai:reembed-products {--sync : Run embeddings inline instead of dispatching to the queue}';

    protected $description = 'Re-generate AI embeddings for all product images (run after switching AI_SIMILARITY_PROVIDER).';

    public function handle(): int
    {
        $count = ProductImage::query()->count();

        if ($count === 0) {
            $this->info('No product images to re-embed.');
            return self::SUCCESS;
        }

        $this->info("Queuing re-embedding for {$count} product image(s)...");

        $sync = (bool) $this->option('sync');

        ProductImage::query()
            ->select('id')
            ->chunkById(200, function ($chunk) use ($sync) {
                foreach ($chunk as $image) {
                    $image->update(['ai_embedding_status' => 'pending']);

                    if ($sync) {
                        GenerateProductImageEmbeddingJob::dispatchSync($image->id);
                    } else {
                        GenerateProductImageEmbeddingJob::dispatch($image->id);
                    }
                }
            });

        if ($sync) {
            $this->info('Done. All embeddings generated inline.');
        } else {
            $this->info('Done. Make sure the "ai" queue worker is running: php artisan queue:work --queue=ai');
        }

        return self::SUCCESS;
    }
}
