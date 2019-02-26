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

    public function pull(array $namespaces)
    {
        if (! $namespaces) {
            return [];
        }
        if (Coroutine::inCoroutine()) {
            $result = $this->coroutinePull($namespaces);
        } else {
            $result = $this->blockingPull($namespaces);
        }
        foreach ($result as $namespace => $configs) {
            if (isset($configs['releaseKey'], $configs['configurations'])) {
                if (isset($this->callbacks[$namespace]) && is_callable($this->callbacks[$namespace])) {
                    call($this->callbacks[$namespace], [$configs]);
                } else {
                    // Call default callback.
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

    protected function coroutinePull(array $namespaces)
    {
        $option = $this->option;
        $parallel = new Parallel();
        $httpClientFactory = $this->httpClientFactory;
        foreach ($namespaces as $namespace) {
            $parallel->add(function () use ($option, $httpClientFactory, $namespace) {
                $client = $httpClientFactory();
                if (! $client instanceof \GuzzleHttp\Client) {
                    throw new \RuntimeException('Invalid http client.');
                }
                $releaseKey = ReleaseKey::get($option->buildCacheKey($namespace), null);
                $response = $client->get($option->buildBaseUrl() . $namespace, [
                    'query' => [
                        'ip' => $option->getClientIp(),
                        'releaseKey' => $releaseKey,
                    ],
                ]);
                if ($response->getStatusCode() === 200 && strpos($response->getHeaderLine('Content-Type'), 'application/json') !== false) {
                    $body = json_decode((string)$response->getBody(), true);
                    $result = [
                        'configurations' => $body['configurations'] ?? [],
                        'releaseKey' => $body['releaseKey'] ?? '',
                    ];
                } else {
                    $result = [
                        'configurations' => [],
                        'releaseKey' => '',
                    ];
                }
                return $result;
            }, $namespace);
        }
        return $parallel->wait();
    }

    protected function blockingPull(array $namespaces)
    {
        $result = [];
        $url = $this->option->buildBaseUrl();
        $httpClientFactory = $this->httpClientFactory;
        foreach ($namespaces as $namespace) {
            $client = $httpClientFactory();
            if (! $client instanceof \GuzzleHttp\Client) {
                throw new \RuntimeException('Invalid http client.');
            }
            $releaseKey = ReleaseKey::get($this->option->buildCacheKey($namespace), null);
            $response = $client->get($url . $namespace, [
                'query' => [
                    'ip' => $this->option->getClientIp(),
                    'releaseKey' => $releaseKey,
                ],
            ]);
            if ($response->getStatusCode() === 200 && strpos($response->getHeaderLine('Content-Type'), 'application/json') !== false) {
                $body = json_decode((string)$response->getBody(), true);
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
