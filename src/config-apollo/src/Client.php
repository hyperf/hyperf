<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\ConfigApollo;

use Closure;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Parallel;
use RuntimeException;

class Client implements ClientInterface
{
    /**
     * @var Option
     */
    private $option;

    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var Closure
     */
    private $httpClientFactory;

    public function __construct(
        Option $option,
        Closure $httpClientFactory
    ) {
        $this->option = $option;
        $this->httpClientFactory = $httpClientFactory;
    }

    public function pull(array $namespaces): array
    {
        if (! $namespaces) {
            return [];
        }
        $result = $this->parallelPull($namespaces);
        return $result;
    }

    public function getOption(): Option
    {
        return $this->option;
    }

    private function hasSecret(): bool
    {
        return ! empty($this->option->getSecret());
    }

    private function getTimestamp(): string
    {
        [$usec, $sec] = explode(' ', microtime());
        return sprintf('%.0f', (floatval($usec) + floatval($sec)) * 1000);
    }

    private function getAuthorization(string $timestamp, string $pathWithQuery): string
    {
        if (! $this->hasSecret()) {
            return '';
        }
        $toSignature = $timestamp . "\n" . $pathWithQuery;
        $signature = base64_encode(hash_hmac('sha1', $toSignature, $this->option->getSecret(), true));
        return sprintf('Apollo %s:%s', $this->option->getAppid(), $signature);
    }

    private function parallelPull(array $namespaces): array
    {
        $option = $this->option;
        $parallel = new Parallel();
        $httpClientFactory = $this->httpClientFactory;
        foreach ($namespaces as $namespace) {
            $parallel->add(function () use ($option, $httpClientFactory, $namespace) {
                $client = $httpClientFactory();
                if (! $client instanceof \GuzzleHttp\Client) {
                    throw new RuntimeException('Invalid http client.');
                }
                $cacheKey = $option->buildCacheKey($namespace);
                $releaseKey = $this->getReleaseKey($cacheKey);
                $query = [
                    'ip' => $option->getClientIp(),
                    'releaseKey' => $releaseKey,
                ];
                $timestamp = $this->getTimestamp();
                $headers = [
                    'Authorization' => $this->getAuthorization($timestamp, parse_url($option->buildBaseUrl(), PHP_URL_PATH) . $namespace . '?' . http_build_query($query)),
                    'Timestamp' => $timestamp,
                ];

                $response = $client->get($option->buildBaseUrl() . $namespace, [
                    'query' => $query,
                    'headers' => $headers,
                ]);
                if ($response->getStatusCode() === 200 && strpos($response->getHeaderLine('Content-Type'), 'application/json') !== false) {
                    $body = json_decode((string) $response->getBody(), true);
                    $result = $body['configurations'] ?? [];
                    $this->cache[$cacheKey] = [
                        'releaseKey' => $body['releaseKey'] ?? '',
                        'configurations' => $result,
                    ];
                } else {
                    // Status code is 304 or Connection Failed, use the previous config value
                    $result = $this->cache[$cacheKey]['configurations'] ?? [];
                    if ($response->getStatusCode() !== 304) {
                        $this->logger->error('Connect to Apollo server failed');
                    }
                }
                return $result;
            }, $namespace);
        }
        return $parallel->wait();
    }

    protected function getReleaseKey(string $cacheKey): ?string
    {
        return $this->cache[$cacheKey]['releaseKey'] ?? null;
    }
}
