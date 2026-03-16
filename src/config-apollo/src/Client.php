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
use Exception;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coroutine\Parallel;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class Client implements ClientInterface
{
    protected array $cache = [];

    public function __construct(
        protected Option $option,
        protected Closure $httpClientFactory,
        protected ConfigInterface $config,
        protected StdoutLoggerInterface $logger
    ) {
    }

    public function pull(): array
    {
        $namespaces = $this->config->get('config_center.drivers.apollo.namespaces');
        return $this->parallelPull($namespaces);
    }

    public function getOption(): Option
    {
        return $this->option;
    }

    public function parallelPull(array $namespaces): array
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
                if ($response->getStatusCode() === 200 && str_contains($response->getHeaderLine('content-type'), 'application/json')) {
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

    public function longPulling(array $notifications): ?ResponseInterface
    {
        $httpClientFactory = $this->httpClientFactory;
        $client = $httpClientFactory([
            'timeout' => 60,
        ]);
        if (! $client instanceof \GuzzleHttp\Client) {
            throw new RuntimeException('Invalid http client.');
        }
        try {
            $uri = $this->option->buildLongPullingBaseUrl();
            return $client->get($uri, [
                'query' => [
                    'appId' => $this->option->getAppid(),
                    'cluster' => $this->option->getCluster(),
                    'notifications' => json_encode(array_values($notifications)),
                ],
            ]);
        } catch (Exception) {
            // Do nothing
            return null;
        }
    }

    protected function getReleaseKey(string $cacheKey): ?string
    {
        return $this->cache[$cacheKey]['releaseKey'] ?? null;
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
}
