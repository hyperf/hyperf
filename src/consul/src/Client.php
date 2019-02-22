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

namespace Hyperf\Consul;

use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use GuzzleHttp\Exception\TransferException;

class Client
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
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function __construct(\Closure $clientFactory, StdoutLoggerInterface $logger = null)
    {
        $this->clientFactory = $clientFactory;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Send a GET request.
     */
    public function get(string $url = null, array $options = [])
    {
        return $this->request('GET', $url, $options);
    }

    /**
     * Send a HEAD request.
     */
    public function head(string $url = null, array $options = [])
    {
        return $this->request('HEAD', $url, $options);
    }

    /**
     * Send a POST request.
     */
    public function post(string $url = null, array $options = [])
    {
        return $this->request('POST', $url, $options);
    }

    /**
     * Send a PUT request.
     */
    public function put(string $url = null, array $options = [])
    {
        return $this->request('PUT', $url, $options);
    }

    /**
     * Send a PATCH request.
     */
    public function patch(string $url = null, array $options = [])
    {
        return $this->request('PATCH', $url, $options);
    }

    /**
     * Send a DELETE request.
     */
    public function delete(string $url = null, array $options = [])
    {
        return $this->request('DELETE', $url, $options);
    }

    /**
     * Send a OPTIONS request.
     */
    public function options(string $url = null, array $options = [])
    {
        return $this->request('OPTIONS', $url, $options);
    }

    /**
     * Send a HTTP request.
     */
    private function request(string $method, string $url, array $options)
    {
        $this->logger->debug(sprintf('Consul Request [%s] %s', strtoupper($method), $url));
        try {
            // Set the default options to the $options.
            if (! isset($options['base_uri'])) {
                $options['base_uri'] = self::DEFAULT_URI;
            }
            // Create a HTTP Client by $clientFactory closure.
            $client = $this->clientFactory($options);
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

        return $response;
    }
}
