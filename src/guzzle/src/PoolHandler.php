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
namespace Hyperf\Guzzle;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\FulfilledPromise;
use Hyperf\Pool\SimplePool\PoolFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class PoolHandler extends CoroutineHandler
{
    /**
     * @var PoolFactory
     */
    protected $factory;

    /**
     * @var array
     */
    protected $option;

    public function __construct(PoolFactory $factory, array $option = [])
    {
        $this->factory = $factory;
        $this->option = $option;
    }

    public function __invoke(RequestInterface $request, array $options)
    {
        $uri = $request->getUri();
        $host = $uri->getHost();
        $port = $uri->getPort();
        $ssl = $uri->getScheme() === 'https';
        $path = $uri->getPath();
        $query = $uri->getQuery();

        if (empty($port)) {
            $port = $ssl ? 443 : 80;
        }
        if (empty($path)) {
            $path = '/';
        }
        if ($query !== '') {
            $path .= '?' . $query;
        }

        $pool = $this->factory->get($this->getPoolName($uri), function () use ($host, $port, $ssl) {
            return $this->makeClient($host, $port, $ssl);
        }, $this->option);

        $connection = $pool->get();

        try {
            $client = $connection->getConnection();
            $headers = $this->initHeaders($request, $options);
            $settings = $this->getSettings($request, $options);
            if (! empty($settings)) {
                $client->set($settings);
            }

            $ms = microtime(true);

            try {
                $raw = $client->request($request->getMethod(), $path, $headers, (string) $request->getBody());
            } catch (\Exception $exception) {
                $connection->close();
                $exception = new ConnectException($exception->getMessage(), $request, null, [
                    'errCode' => $exception->getCode(),
                ]);
                return Create::rejectionFor($exception);
            }

            $response = $this->getResponse($raw, $request, $options, microtime(true) - $ms);
        } finally {
            $connection->release();
        }

        return new FulfilledPromise($response);
    }

    protected function getPoolName(UriInterface $uri)
    {
        return sprintf('guzzle.handler.%s.%d.%s', $uri->getHost(), $uri->getPort(), $uri->getScheme());
    }
}
