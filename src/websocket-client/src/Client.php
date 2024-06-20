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

namespace Hyperf\WebSocketClient;

use Hyperf\HttpMessage\Server\Response;
use Hyperf\WebSocketClient\Exception\ConnectException;
use Psr\Http\Message\UriInterface;
use Swoole\Coroutine;
use Swoole\WebSocket\Frame as SwFrame;

class Client
{
    protected Coroutine\Http\Client $client;

    public function __construct(protected UriInterface $uri, array $headers = [])
    {
        $host = $uri->getHost();
        $port = $uri->getPort();
        $ssl = $uri->getScheme() === 'wss';

        if (empty($port)) {
            $port = $ssl ? 443 : 80;
        }

        $this->client = new Coroutine\Http\Client($host, $port, $ssl);
        $headers && $this->client->setHeaders($headers);
        parse_str($this->uri->getQuery(), $query);

        $query = http_build_query($query);

        $path = $this->uri->getPath() ?: '/';
        $path = empty($query) ? $path : $path . '?' . $query;

        $ret = $this->client->upgrade($path);
        if (! $ret) {
            if ($this->client->errCode !== 0) {
                $errCode = $this->client->errCode;
                $errMsg = $this->client->errMsg;
            } else {
                $errCode = $this->client->statusCode;
                $errMsg = Response::getReasonPhraseByCode($errCode);
            }

            throw new ConnectException(sprintf('Websocket upgrade failed by [%s] [%s].', $errCode, $errMsg));
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    public function recv(float $timeout = -1)
    {
        $ret = $this->client->recv($timeout);
        if ($ret instanceof SwFrame) {
            return new Frame($ret);
        }

        return $ret;
    }

    /**
     * @param int $flags SWOOLE_WEBSOCKET_FLAG_FIN or SWOOLE_WEBSOCKET_FLAG_COMPRESS
     */
    public function push(string $data, int $opcode = WEBSOCKET_OPCODE_TEXT, ?int $flags = null): bool
    {
        return $this->client->push($data, $opcode, $flags);
    }

    public function close(): bool
    {
        return $this->client->close();
    }
}
