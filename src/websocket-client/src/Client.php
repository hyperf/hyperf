<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\WebSocketClient;

use Hyperf\WebSocketClient\Exception\ConnectException;
use Psr\Http\Message\UriInterface;
use Swoole\Coroutine;

class Client
{
    /**
     * @var UriInterface
     */
    protected $uri;

    /**
     * @var Coroutine\Http\Client
     */
    protected $client;

    public function __construct(UriInterface $uri)
    {
        $this->uri = $uri;
        $host = $uri->getHost();
        $port = $uri->getPort();
        $ssl = $uri->getScheme() === 'wss';

        $this->client = new Coroutine\Http\Client($host, $port, $ssl);

        parse_str($this->uri->getQuery(), $query);
        $query = $this->getQueryParams() + $query;
        $query = http_build_query($query);

        $path = $this->uri->getPath() ?: '/';
        $path = empty($query) ? $path : $path . '?' . $query;

        $ret = $this->client->upgrade($path);
        if (! $ret) {
            throw new ConnectException('Websocket upgrade failed by [' . swoole_strerror($this->client->errCode) . '].');
        }
    }
}
