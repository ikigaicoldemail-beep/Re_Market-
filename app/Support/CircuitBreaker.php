<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Cache-backed circuit breaker for external integrations.
 *
 * Opens after $failureThreshold failures within $failureWindowSeconds.
 * Stays open for $cooldownSeconds, then half-opens for one trial call.
 * On trial success the breaker fully closes; on trial failure it reopens.
 */
class CircuitBreaker
{
    public function __construct(
        private readonly string $key,
        private readonly int $failureThreshold = 5,
        private readonly int $failureWindowSeconds = 60,
        private readonly int $cooldownSeconds = 60,
    ) {}

    /**
     * Run a callable behind the breaker. Throws CircuitBreakerOpenException
     * immediately when the circuit is open. Returns the callable's value
     * otherwise.
     *
     * @template T
     * @param  callable(): T $callable
     * @return T
     */
    public function call(callable $callable): mixed
    {
        $state = Cache::get($this->stateKey(), 'closed');

        if ($state === 'open') {
            throw new CircuitBreakerOpenException(
                "Circuit '{$this->key}' is open. Try again later."
            );
        }

        try {
            $result = $callable();
            if ($state === 'half-open') {
                $this->reset();
            }
            return $result;
        } catch (Throwable $e) {
            $this->recordFailure($e);
            throw $e;
        }
    }

    public function reset(): void
    {
        Cache::forget($this->stateKey());
        Cache::forget($this->failuresKey());
    }

    private function recordFailure(Throwable $e): void
    {
        // Sliding failure window. Using get+put keeps the implementation
        // portable across cache drivers (database, redis, file, array).
        $count = (int) Cache::get($this->failuresKey(), 0) + 1;
        Cache::put($this->failuresKey(), $count, $this->failureWindowSeconds);

        if ($count >= $this->failureThreshold) {
            Cache::put($this->stateKey(), 'open', $this->cooldownSeconds);

            Log::warning("Circuit breaker opened for '{$this->key}'", [
                'failures' => $count,
                'last_error' => $e->getMessage(),
            ]);
        }
    }

    private function stateKey(): string
    {
        return 'cb:'.$this->key.':state';
    }

    private function failuresKey(): string
    {
        return 'cb:'.$this->key.':failures';
    }
}
