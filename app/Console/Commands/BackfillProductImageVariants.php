<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use App\Services\ImageVariantService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackfillProductImageVariants extends Command
{
    protected $signature = 'images:backfill-variants
                            {--force : Re-generate variants even if they already exist}
                            {--limit=0 : Only process N images (0 = all)}';

    protected $description = 'Generate thumb/card/large + WebP variants for existing product images';

    public function handle(ImageVariantService $variants): int
    {
        $query = ProductImage::query();
        if (! $this->option('force')) {
            $query->whereNull('variants');
        }
        if ((int) $this->option('limit') > 0) {
            $query->limit((int) $this->option('limit'));
        }

        $total = $query->count();
        if ($total === 0) {
            $this->info('No product images need variants. Pass --force to regenerate.');
            return self::SUCCESS;
        }

        $this->info("Processing {$total} image(s)...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $ok = 0;
        $failed = 0;

        $query->chunkById(50, function ($images) use ($variants, $bar, &$ok, &$failed) {
            foreach ($images as $image) {
                try {
                    if (! Storage::disk($image->disk)->exists($image->path)) {
                        $failed++;
                        $bar->advance();
                        continue;
                    }
                    $binary = Storage::disk($image->disk)->get($image->path);
                    $variantMap = $variants->generateFromBinary($binary, $image->disk, $image->path);
                    $image->variants = $variantMap;
                    $image->save();
                    $ok++;
                } catch (\Throwable $e) {
                    $failed++;
                    $this->newLine();
                    $this->error("Image #{$image->id}: ".$e->getMessage());
                }
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Success: {$ok}, Failed: {$failed}.");

        return self::SUCCESS;
    }
}
