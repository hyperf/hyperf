<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\ConfigApollo;

use Closure;
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

    /**
     * @var array
     */
    private $notifications;

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

    public function pull(array $namespaces, array $callbacks = []): void
    {
        if (! $namespaces) {
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

    private function coroutinePull(array $namespaces): array
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
                    $body = json_decode((string) $response->getBody(), true);
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

    private function blockingPull(array $namespaces): array
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

    public function fetch(array $namespaces, array $callbacks = []): void
    {
        $httpClientFactory = $this->httpClientFactory;
        $client = $httpClientFactory([
            'timeout' => $this->option->getIntervalTimeout(),
        ]);
        if (!$client instanceof \GuzzleHttp\Client) {
            throw new \RuntimeException('Invalid http client.');
        }
        foreach ($namespaces as $namespace) {
            if (!isset($this->notifications[$namespace])) {
                $this->notifications[$namespace] = ['namespaceName' => $namespace, 'notificationId' => -1];
            }
        }

        while (true) {
            $url = sprintf('%s/notifications/v2?%s',
                $this->option->getServer(),
                http_build_query([
                    'appId' => $this->option->getAppid(),
                    'cluster' => $this->option->getCluster(),
                    'notifications' => json_encode(array_values($this->notifications)),
                ])
            );

            // Ignore the timeout error
            try {
                $response = $client->get($url);
                if ($response->getStatusCode() === 200) {
                    $notifications = json_decode((string)$response->getBody(), true);
                    // Ignore the first pull
                    if (!empty($this->notifications) && current($this->notifications)['notificationId'] !== -1) {
                        $this->pull($namespaces, $callbacks);
                    }
                    array_walk($notifications, function (&$notification) {
                        unset($notification['messages']);
                    });
                    $this->notifications = array_merge($this->notifications, array_column($notifications, null, 'namespaceName'));
                } elseif ($response['statusCode'] === 304) {
                    // ignore 304
                }
            } catch (\Exception $exception) {
            }

        }
    }

}
