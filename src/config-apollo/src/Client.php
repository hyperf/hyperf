<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ConfigApollo;

use Closure;
use Hyperf\Utils\Parallel;
use Hyperf\Utils\Coroutine;
use Hyperf\Contract\ConfigInterface;

class Client
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
    ) {
        $this->option = $option;
        $this->callbacks = $callbacks;
        $this->httpClientFactory = $httpClientFactory;
        $this->config = $config;
    }

    public function pull(array $namespaces, bool $withCache = true)
    {
        if (! $namespaces) {
            return [];
        }
        if (Coroutine::inCoroutine()) {
            // @todo needs test.
            $result = $this->coroutinePull($namespaces, $withCache);
        } else {
            $result = $this->blockingPull($namespaces, $withCache);
        }
        foreach ($result as $namespace => $value) {
            if (isset($value['releaseKey'], $value['configurations']) && $value['releaseKey'] && $value['configurationss']) {
                if (isset($this->callbacks[$namespace]) && is_callable($this->callbacks[$namespace])) {
                    call($this->callbacks[$namespace], [$value]);
                } else {
                    // Call default callback.
                    if ($this->config instanceof ConfigInterface) {
                        foreach ($configs['configurations'] ?? [] as $key => $value) {
                            $this->config->set($key, $value);
                        }
                    }
                }
                ReleaseKey::set($this->option->buildCacheKey($namespace), $value['releaseKey']);
            }
        }
    }

    protected function coroutinePull(array $namespaces, bool $withCache = true)
    {
        $option = $this->option;
        $parallel = new Parallel();
        $httpClientFactory = $this->httpClientFactory;
        foreach ($namespaces as $namespace) {
            $parallel->add(function () use ($option, $withCache, $httpClientFactory, $option, $namespace) {
                $client = $httpClientFactory();
                if (! $client instanceof \GuzzleHttp\Client) {
                    throw new \RuntimeException('Invalid http client.');
                }
                $releaseKey = null;
                ! $withCache && $releaseKey = ReleaseKey::get($option->buildCacheKey($namespace), null);
                $response = $client->get($option->buildBaseUrl($withCache) . $namespace, [
                    'query' => [
                        'ip' => $option->getClientIp(),
                        'releaseKey' => $releaseKey,
                    ],
                ]);
                if ($response->getStatusCode() === 200 && strpos($response->getHeaderLine('Content-Type'), 'application/json') !== false) {
                    $body = json_decode((string) $response->getBody(), true);
                    $result[$namespace] = [
                        'configurations' => $body['configurations'] ?? [],
                        'releaseKey' => $body['releaseKey'] ?? '',
                    ];
                } else {
                    $result[$namespace] = [
                        'configurations' => [],
                        'releaseKey' => '',
                    ];
                }
            });
        }
        return $parallel->wait();
    }

    protected function blockingPull(array $namespaces, bool $withCache = true)
    {
        $result = [];
        $url = $this->option->buildBaseUrl($withCache);
        $httpClientFactory = $this->httpClientFactory;
        foreach ($namespaces as $namespace) {
            $client = $httpClientFactory();
            if (! $client instanceof \GuzzleHttp\Client) {
                throw new \RuntimeException('Invalid http client.');
            }
            $releaseKey = null;
            ! $withCache && $releaseKey = ReleaseKey::get($this->option->buildCacheKey($namespace), null);
            $response = $client->get($url . $namespace, [
                'query' => [
                    'ip' => $this->option->getClientIp(),
                    'releaseKey' => $releaseKey,
                ],
            ]);
            if ($response->getStatusCode() === 200 && strpos($response->getHeaderLine('Content-Type'), 'application/json') !== false) {
                $body = json_decode((string) $response->getBody(), true);
                $result[$namespace] = [
                    'configurations' => $body['configurations'] ?? [],
                    'releaseKey' => $body['releaseKey'] ?? '',
                ];
            } else {
                $result[$namespace] = [
                    'configurations' => [],
                    'releaseKey' => '',
                ];
            }
        }
        return $result;
    }
}
