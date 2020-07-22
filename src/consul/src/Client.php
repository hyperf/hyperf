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
namespace Hyperf\Consul;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use Hyperf\Consul\Exception\ClientException;
use Hyperf\Consul\Exception\ServerException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class Client
{
    const DEFAULT_URI = 'http://127.0.0.1:8500';

    /**
     * Will execute this closure everytime when the consul client send a HTTP request,
     * and the closure should return a GuzzleHttp\ClientInterface instance.
     * $clientFactory(array $options).
     *
     * @var \Closure
     */
    private $clientFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(\Closure $clientFactory, LoggerInterface $logger = null)
    {
        $this->clientFactory = $clientFactory;
        $this->logger = $logger ?: new NullLogger();
    }

    protected function resolveOptions(array $options, array $availableOptions): array
    {
        // Add key of ACL token to $availableOptions
        $availableOptions[] = 'token';

        return array_intersect_key($options, array_flip($availableOptions));
    }

    /**
     * Send a HTTP request.
     */
    protected function request(string $method, string $url, array $options = []): ConsulResponse
    {
        $this->logger->debug(sprintf('Consul Request [%s] %s', strtoupper($method), $url));
        try {
            // Create a HTTP Client by $clientFactory closure.
            $clientFactory = $this->clientFactory;
            $client = $clientFactory($options);
            if (! $client instanceof ClientInterface) {
                throw new ClientException(sprintf('The client factory should create a %s instance.', ClientInterface::class));
            }
            $response = $client->request($method, $url, $options);
        } catch (TransferException $e) {
            $message = sprintf('Something went wrong when calling consul (%s).', $e->getMessage());
            $this->logger->error($message);
            throw new ServerException($e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() >= 400) {
            $message = sprintf('Something went wrong when calling consul (%s - %s).', $response->getStatusCode(), $response->getReasonPhrase());
            $this->logger->error($message);
            $message .= PHP_EOL . (string) $response->getBody();
            if ($response->getStatusCode() >= 500) {
                throw new ServerException($message, $response->getStatusCode());
            }
            throw new ClientException($message, $response->getStatusCode());
        }

        return new ConsulResponse($response);
    }
}
