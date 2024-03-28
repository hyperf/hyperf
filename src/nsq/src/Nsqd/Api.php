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

namespace Hyperf\Nsq\Nsqd;

use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class Api extends AbstractEndpoint
{
    public function stats(string $format = 'text', ?string $topic = null, ?string $channel = null): ResponseInterface
    {
        $query = [
            'format' => $format,
        ];
        ! is_null($topic) && $query['topic'] = $topic;
        ! is_null($channel) && $query['channel'] = $channel;
        return $this->client->request('GET', '/stats', [
            RequestOptions::QUERY => $query,
        ]);
    }

    public function ping(): bool
    {
        $response = $this->client->request('GET', '/ping');
        return $response->getStatusCode() === 200;
    }

    public function info(): ResponseInterface
    {
        return $this->client->request('GET', '/info');
    }

    public function debugPprof(): ResponseInterface
    {
        return $this->client->request('GET', '/debug/pprof');
    }

    public function debugPprofProfile(): ResponseInterface
    {
        return $this->client->request('GET', '/debug/pprof/profile');
    }

    public function debugPprofGoroutine(): ResponseInterface
    {
        return $this->client->request('GET', '/debug/pprof/goroutine');
    }

    public function debugPprofHeap(): ResponseInterface
    {
        return $this->client->request('GET', '/debug/pprof/heap');
    }

    public function debugPprofBlock(): ResponseInterface
    {
        return $this->client->request('GET', '/debug/pprof/block');
    }

    public function debugPprofThreadCreate(): ResponseInterface
    {
        return $this->client->request('GET', '/debug/pprof/threadcreate');
    }

    public function getConfigNsqlookupdTcpAddresses(): ResponseInterface
    {
        return $this->client->request('GET', '/config/nsqlookupd_tcp_addresses');
    }

    public function setConfigNsqlookupdTcpAddresses(array $addresses): bool
    {
        $response = $this->client->request('PUT', '/config/nsqlookupd_tcp_addresses', [
            RequestOptions::JSON => $addresses,
        ]);
        $statusCode = $response->getStatusCode();
        return $statusCode >= 200 && $statusCode < 300;
    }
}
