<?php

namespace App\Services;

use App\Contracts\SocialPlatformClientInterface;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class SocialPlatformManager
{
    /**
     * @param  iterable<SocialPlatformClientInterface>  $clients
     */
    public function __construct(private readonly iterable $clients)
    {
    }

    public function forPlatform(string $platform): SocialPlatformClientInterface
    {
        $client = collect($this->clients)
            ->first(fn (SocialPlatformClientInterface $client) => $client->platform() === $platform);

        if (! $client) {
            throw new InvalidArgumentException("No social platform client is registered for [{$platform}].");
        }

        return $client;
    }

    public function platforms(): Collection
    {
        return collect($this->clients)->map->platform()->values();
    }
}
