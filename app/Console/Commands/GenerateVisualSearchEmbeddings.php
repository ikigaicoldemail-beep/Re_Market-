<?php

namespace App\Console\Commands;

use App\Services\VisualSearchService;
use Illuminate\Console\Command;

class GenerateVisualSearchEmbeddings extends Command
{
    protected $signature = 'visual-search:generate-embeddings
                            {--limit=0 : Only process N product images (0 = all)}';

    protected $description = 'Generate local image features and rebuild the FAISS visual search index';

    public function handle(VisualSearchService $visualSearch): int
    {
        $limit = (int) $this->option('limit');

        $this->info('Rebuilding visual search embeddings with local image features + FAISS...');

        $result = $visualSearch->rebuildIndex($limit > 0 ? $limit : null);

        $this->info("Done. Indexed: {$result['indexed']}. Failed: {$result['failed']}.");

        return self::SUCCESS;
    }
}
