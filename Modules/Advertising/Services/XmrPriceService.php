<?php

namespace Modules\Advertising\Services;

class XmrPriceService
{
    /** @var string */
    private $cacheFile;

    /** @var int */
    private $ttl;

    /** @var int */
    private $timeout;

    /** @var string */
    private $primaryApi;

    /** @var string */
    private $fallbackApi;

    public function __construct()
    {
        $this->cacheFile   = storage_path('app/xmr_price_cache.json');
        $this->ttl         = (int) config('advertising.price_cache_ttl', 60);
        $this->timeout     = (int) config('advertising.fetch_timeout', 10);
        $this->primaryApi  = (string) config('advertising.price_api_primary');
        $this->fallbackApi = (string) config('advertising.price_api_fallback');
    }

    /**
     * Fetch the current XMR/USD price.
     *
     * Returns an array with:
     *   'price'     => float        USD price per XMR
     *   'error'     => string|null  Error message if retrieval failed
     *   'cache_age' => int          Seconds since the cached value was written
     *
     * @return array{price: float, error: string|null, cache_age: int}
     */
    public function fetchPrice(): array
    {
        // Try to serve from cache first.
        $cached = $this->readCache();

        if ($cached !== null && $cached['cache_age'] < $this->ttl) {
            return $cached;
        }

        // Attempt live fetch.
        $price = $this->fetchFromPrimary();
        $error = null;

        if ($price === null) {
            $price = $this->fetchFromFallback();
        }

        if ($price === null) {
            $error = 'Unable to retrieve XMR price from any source.';

            // Fall back to stale cache rather than returning 0.
            if ($cached !== null) {
                return array_merge($cached, ['error' => $error]);
            }

            return ['price' => 0.0, 'error' => $error, 'cache_age' => 0];
        }

        $this->writeCache($price);

        return ['price' => $price, 'error' => null, 'cache_age' => 0];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Fetch from CoinGecko (primary).
     *
     * @return float|null
     */
    private function fetchFromPrimary(): ?float
    {
        try {
            $body = $this->httpGet($this->primaryApi);

            if ($body === null) {
                return null;
            }

            $data = json_decode($body, true);

            if (isset($data['monero']['usd'])) {
                return (float) $data['monero']['usd'];
            }
        } catch (\Throwable $e) {
            // Intentionally suppressed – will try fallback.
        }

        return null;
    }

    /**
     * Fetch from CryptoCompare (fallback).
     *
     * @return float|null
     */
    private function fetchFromFallback(): ?float
    {
        try {
            $body = $this->httpGet($this->fallbackApi);

            if ($body === null) {
                return null;
            }

            $data = json_decode($body, true);

            if (isset($data['USD'])) {
                return (float) $data['USD'];
            }
        } catch (\Throwable $e) {
            // Intentionally suppressed.
        }

        return null;
    }

    /**
     * Perform an HTTP GET request using GuzzleHttp when available, falling back
     * to file_get_contents.
     *
     * @param string $url
     * @return string|null
     */
    private function httpGet(string $url): ?string
    {
        // Prefer GuzzleHTTP (available as a Laravel dependency).
        if (class_exists(\GuzzleHttp\Client::class)) {
            $client   = new \GuzzleHttp\Client(['timeout' => $this->timeout]);
            $response = $client->get($url);

            return (string) $response->getBody();
        }

        // file_get_contents fallback.
        $ctx = stream_context_create([
            'http' => [
                'timeout' => $this->timeout,
                'method'  => 'GET',
            ],
        ]);

        $body = @file_get_contents($url, false, $ctx);

        return $body !== false ? $body : null;
    }

    /**
     * Read the cache file with an exclusive file lock.
     *
     * @return array{price: float, error: null, cache_age: int}|null
     */
    private function readCache(): ?array
    {
        if (!file_exists($this->cacheFile)) {
            return null;
        }

        $fh = @fopen($this->cacheFile, 'rb');

        if ($fh === false) {
            return null;
        }

        $result = null;

        if (flock($fh, LOCK_SH)) {
            $raw  = stream_get_contents($fh);
            flock($fh, LOCK_UN);

            $data = json_decode($raw, true);

            if (isset($data['price'], $data['written_at'])) {
                $age    = time() - (int) $data['written_at'];
                $result = [
                    'price'     => (float) $data['price'],
                    'error'     => null,
                    'cache_age' => max(0, $age),
                ];
            }
        }

        fclose($fh);

        return $result;
    }

    /**
     * Write a price value to the cache file with an exclusive file lock.
     *
     * @param float $price
     * @return void
     */
    private function writeCache(float $price): void
    {
        $dir = dirname($this->cacheFile);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fh = @fopen($this->cacheFile, 'c+b');

        if ($fh === false) {
            return;
        }

        if (flock($fh, LOCK_EX)) {
            ftruncate($fh, 0);
            rewind($fh);
            fwrite($fh, json_encode([
                'price'      => $price,
                'written_at' => time(),
            ]));
            flock($fh, LOCK_UN);
        }

        fclose($fh);
    }
}
