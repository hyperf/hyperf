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
use GuzzleHttp\Exception\RequestException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Codec\Json;
use Hyperf\Utils\Coroutine;
use Hyperf\Utils\Exception\ParallelExecutionException;
use Hyperf\Utils\Parallel;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

abstract class AbstractClient implements ClientInterface
{
    /**
     * @var Option
     */
    protected $option;

    /**
     * @var array
     */
    private $callbacks;

    /**
     * @var Closure
     */
    protected $httpClientFactory;

    /**
     * @var null|ConfigInterface
     */
    protected $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        Option $option,
        array $callbacks = [],
        Closure $httpClientFactory,
        ?ConfigInterface $config = null,
        ?LoggerInterface $logger = null
    ) {
        $this->option = $option;
        $this->callbacks = $callbacks;
        $this->httpClientFactory = $httpClientFactory;
        $this->config = $config;
        $this->logger = $logger;
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
        try {
            $result = $parallel->wait();
        } catch (ParallelExecutionException $parallelExecutionException) {
            foreach ($parallelExecutionException->getThrowables() as $throwable) {
                $message = $this->prehandleException($throwable);
                $this->logger->error($message);
            }
            $result = [];
        }
        return $result;
    }

    protected function prehandleException(\Throwable $throwable): string
    {
        if ($throwable instanceof RequestException) {
            $responseContent = $throwable->getResponse()->getBody()->getContents();
            try {
                $structMessage = Json::decode($responseContent);
                return sprintf('Apollo Exception, Status: %d, Message: %s', $structMessage['status'] ?? 0, $structMessage['message'] ?? '');
            } catch (InvalidArgumentException $exception) {
                return '';
            }
        }
        return $throwable->getMessage();
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

}
