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

    public function __construct(
        Option $option,
        array $callbacks = [],
        Closure $httpClientFactory
    ) {
        $this->option = $option;
        $this->callbacks = $callbacks;
        $this->httpClientFactory = $httpClientFactory;
    }

    public function pull(array $namespaces)
    {
        if (! $namespaces) {
            return [];
        }
        if (Coroutine::inCoroutine()) {
            // @todo needs test.
            $result = $this->coroutinePull($namespaces);
        } else {
            $result = $this->blockingPull($namespaces);
        }
        foreach ($result as $namespace => $value) {
            if (isset($this->callbacks[$namespace]) && is_callable($this->callbacks[$namespace])) {
                call($this->callbacks[$namespace], [$value]);
                if (isset($value['releaseKey']) && $value['releaseKey']) {
                    ReleaseKey::set($namespace, $value['releaseKey']);
                }
            }
        }
    }

    protected function coroutinePull(array $namespaces)
    {
        $option = $this->option;
        $parallel = new Parallel();
        $httpClientFactory = $this->httpClientFactory;
        foreach ($namespaces as $namespace) {
            $parallel->add(function () use ($httpClientFactory, $option, $namespace) {
                $client = $httpClientFactory();
                if (! $client instanceof \GuzzleHttp\Client) {
                    throw new \RuntimeException('Invalid http client.');
                }
                $releaseKey = ReleaseKey::get($namespace, null);
                $response = $client->get($option->buildBaseUrl() . $namespace, [
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
            $releaseKey = ReleaseKey::get($namespace, null);
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
