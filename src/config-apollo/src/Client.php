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
use GuzzleHttp\Exception\ClientException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Parallel;

class Client implements ClientInterface
{
    /**
     * @var Option
     */
    private $option;

    /**
     * @var array
     */
    private $callbacks;

    /**
     * @var Closure
     */
    private $httpClientFactory;

    /**
     * @var null|ConfigInterface
     */
    private $config;

    public function __construct(
        Option $option,
        array $callbacks = [],
        Closure $httpClientFactory,
        ?ConfigInterface $config = null
    )
    {
        $this->option = $option;
        $this->callbacks = $callbacks;
        $this->httpClientFactory = $httpClientFactory;
        $this->config = $config;
    }

    public function pull(array $namespaces, array $callbacks = []): void
    {
        if (!$namespaces) {
            return;
        }
        if (Coroutine::inCoroutine()) {
            $result = $this->coroutinePull($namespaces);
        } else {
            $result = $this->blockingPull($namespaces);
        }
        foreach ($result as $namespace => $configs) {
            if (isset($configs['releaseKey'], $configs['configurations'])) {
                if (isset($callbacks[$namespace])) {
                    // Call the method level callbacks.
                    call($callbacks[$namespace], [$configs, $namespace]);
                } elseif (isset($this->callbacks[$namespace]) && is_callable($this->callbacks[$namespace])) {
                    // Call the config level callbacks.
                    call($this->callbacks[$namespace], [$configs, $namespace]);
                } else {
                    // Call the default callback.
                    if ($this->config instanceof ConfigInterface) {
                        foreach ($configs['configurations'] ?? [] as $key => $value) {
                            $this->config->set($key, $value);
                        }
                    }
                }
                ReleaseKey::set($this->option->buildCacheKey($namespace), $configs['releaseKey']);
            }
        }
    }

    public function getOption(): Option
    {
        return $this->option;
    }

    private function hasSecret()
    {
        return !empty($this->option->getSecret());
    }

    private function getTimestamp()
    {
        list($usec, $sec) = explode(" ", microtime());
        return sprintf('%.0f', (floatval($usec) + floatval($sec)) * 1000);
    }

    private function getAuthorization($timestamp, $pathWithQuery)
    {
        if (!$this->hasSecret()) {
            return '';
        }
        $toSignature = $timestamp . "\n" . $pathWithQuery;
        $signature = base64_encode(hash_hmac('sha1', $toSignature, $this->option->getSecret(), true));
        return sprintf('Apollo %s:%s', $this->option->getAppid(), $signature);
    }


    private function coroutinePull(array $namespaces): array
    {
        $option = $this->option;
        $parallel = new Parallel();
        $httpClientFactory = $this->httpClientFactory;
        foreach ($namespaces as $namespace) {
            $parallel->add(function () use ($option, $httpClientFactory, $namespace) {
                $client = $httpClientFactory();
                if (!$client instanceof \GuzzleHttp\Client) {
                    throw new \RuntimeException('Invalid http client.');
                }
                $releaseKey = ReleaseKey::get($option->buildCacheKey($namespace), null);
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
                    $body = json_decode((string)$response->getBody(), true);
                    $result = [
                        'configurations' => $body['configurations'] ?? [],
                        'releaseKey' => $body['releaseKey'] ?? '',
                    ];
                } else {
                    // The status code is not 200 when the config is not modified in apollo.
                    // So, we shouldn't change the configurations.
                    $result = [];
                }
                return $result;
            }, $namespace);
        }
        return $parallel->wait();
    }

    private function blockingPull(array $namespaces): array
    {
        $result = [];
        $url = $this->option->buildBaseUrl();
        $httpClientFactory = $this->httpClientFactory;
        foreach ($namespaces as $namespace) {
            $client = $httpClientFactory();
            if (!$client instanceof \GuzzleHttp\Client) {
                throw new \RuntimeException('Invalid http client.');
            }
            $releaseKey = ReleaseKey::get($this->option->buildCacheKey($namespace), null);
            $query = [
                'ip' => $this->option->getClientIp(),
                'releaseKey' => $releaseKey,
            ];
            $timestamp = $this->getTimestamp();
            $headers = [
                'Authorization' => $this->getAuthorization($timestamp, parse_url($url, PHP_URL_PATH) . $namespace . '?' . http_build_query($query)),
                'Timestamp' => $timestamp,
            ];

            $response = $client->get($url . $namespace, [
                'query' => $query,
                'headers' => $headers,
            ]);
            if ($response->getStatusCode() === 200 && strpos($response->getHeaderLine('Content-Type'), 'application/json') !== false) {
                $body = json_decode((string)$response->getBody(), true);
                $result[$namespace] = [
                    'configurations' => $body['configurations'] ?? [],
                    'releaseKey' => $body['releaseKey'] ?? '',
                ];
            } else {
                // The status code is not 200 when the config is not modified in apollo.
                // So, we shouldn't change the configurations.
                $result[$namespace] = [];
            }
        }
        return $result;
    }
}
