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

namespace Hyperf\Guzzle\RingPHP;

use Exception;
use GuzzleHttp\Ring\Core;
use GuzzleHttp\Ring\Exception\RingException;
use Hyperf\Engine\Http\Client;
use Hyperf\Pool\SimplePool\PoolFactory;

class PoolHandler extends CoroutineHandler
{
    public function __construct(protected PoolFactory $factory, array $option = [])
    {
        parent::__construct($option);
    }

    public function __invoke($request)
    {
        $method = $request['http_method'] ?? 'GET';
        $scheme = $request['scheme'] ?? 'http';
        $ssl = $scheme === 'https';
        $body = $request['body'] ?? '';
        $effectiveUrl = Core::url($request);
        $params = parse_url($effectiveUrl);
        $host = $params['host'];
        if (! isset($params['port'])) {
            $params['port'] = $this->getPort($request, $ssl);
        }
        $port = $params['port'];
        $path = $params['path'] ?? '/';
        if (isset($params['query']) && is_string($params['query'])) {
            $path .= '?' . $params['query'];
        }

        $pool = $this->factory->get($this->getPoolName($host, $port), function () use ($host, $port, $ssl) {
            return $this->makeClient($host, $port, $ssl);
        }, $this->options);

        $connection = $pool->get();
        $response = null;
        try {
            /** @var Client $client */
            $client = $connection->getConnection();
            // Init Headers
            $headers = $this->initHeaders($request);
            $settings = $this->getSettings($this->options);
            if (! empty($settings)) {
                $client->set($settings);
            }

            $beginTime = microtime(true);

            try {
                $raw = $client->request($method, $path, $headers, (string) $body);
            } catch (Exception $exception) {
                $connection->close();
                $exception = new RingException($exception->getMessage());
                return $this->getErrorResponse($exception, $beginTime, $effectiveUrl);
            }

            $response = $this->getResponse($raw, $beginTime, $effectiveUrl);
        } finally {
            $connection->release();
        }

        return $response;
    }

    protected function getPoolName($host, $port)
    {
        return sprintf('guzzle.ring.handler.%s.%d', $host, $port);
    }
}
