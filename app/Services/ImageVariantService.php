<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class ImageVariantService
{
    /**
     * Variant definitions. Keys are variant slugs; values are [maxLongEdge].
     * The "original" copy is stored separately by the caller; we only add
     * resized derivatives plus a WebP version of each.
     */
    public const VARIANTS = [
        'thumb' => 200,
        'card' => 400,
        'large' => 1200,
    ];

    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new GdDriver());
    }

    /**
     * Generate resized variants + WebP copies for an uploaded image.
     * Variants are written to the same disk as the original under
     * `variants/<sourceName>/<variant>.{jpg|webp}`. Returns a map suitable
     * for storage in product_images.variants:
     *
     *   [
     *     'thumb'      => 'variants/abc.jpg/thumb.jpg',
     *     'thumb_webp' => 'variants/abc.jpg/thumb.webp',
     *     'card'       => 'variants/abc.jpg/card.jpg',
     *     'card_webp'  => 'variants/abc.jpg/card.webp',
     *     'large'      => 'variants/abc.jpg/large.jpg',
     *     'large_webp' => 'variants/abc.jpg/large.webp',
     *   ]
     */
    public function generateFromUpload(UploadedFile $file, string $disk, string $originalRelativePath): array
    {
        $binary = file_get_contents($file->getRealPath());

        return $this->generateFromBinary($binary, $disk, $originalRelativePath);
    }

    /**
     * Generate variants from raw image bytes. Used by the backfill command.
     */
    public function generateFromBinary(string $binary, string $disk, string $originalRelativePath): array
    {
        $baseDir = 'variants/'.$this->safeNameFromPath($originalRelativePath);
        $variants = [];

        foreach (self::VARIANTS as $name => $maxLongEdge) {
            $image = $this->manager->read($binary);
            $image->scaleDown(width: $maxLongEdge, height: $maxLongEdge);

            $jpegPath = $baseDir.'/'.$name.'.jpg';
            $webpPath = $baseDir.'/'.$name.'.webp';

            Storage::disk($disk)->put($jpegPath, (string) $image->toJpeg(quality: 82));
            Storage::disk($disk)->put($webpPath, (string) $image->toWebp(quality: 82));

            $variants[$name] = $jpegPath;
            $variants[$name.'_webp'] = $webpPath;
        }

        return $variants;
    }

    public function delete(string $disk, ?array $variants): void
    {
        if (! $variants) {
            return;
        }

        $paths = array_values(array_filter($variants, fn ($v) => is_string($v) && $v !== ''));
        if ($paths !== []) {
            Storage::disk($disk)->delete($paths);
        }
    }

    private function safeNameFromPath(string $path): string
    {
        $base = pathinfo($path, PATHINFO_FILENAME);

        return Str::slug($base) ?: Str::random(12);
    }
}
