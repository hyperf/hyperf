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

namespace Hyperf\Nsq;

use Swoole\Coroutine\Socket;

class Subscriber
{
    const TYPE_RESPONSE = 0;

    const TYPE_ERROR = 1;

    const TYPE_MESSAGE = 2;

    /**
     * @var \Swoole\Coroutine\Socket
     */
    protected $socket;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $payload;

    public function __construct(Socket $socket)
    {
        $this->socket = $socket;
    }

    public function recv()
    {
        $data = $this->socket->recv(8);
        $this->size = sprintf('%u', unpack('N', substr($data, 0, 4))[1]);
        $this->type = sprintf('%u', unpack('N', substr($data, 4, 4))[1]);
        $data = $this->socket->recv($this->size - 4);
        $this->payload = Packer::unpackString($data);
        return $this;
    }

    public function getMessage(): Message
    {
        return new Message($this->payload);
    }

    public function isMessage()
    {
        return $this->type == self::TYPE_MESSAGE;
    }

    public function isHeartbeat()
    {
        return $this->isMatchResponse('_heartbeat_');
    }

    public function isOk()
    {
        return $this->isMatchResponse('OK');
    }

    private function isMatchResponse($response): bool
    {
        return ! is_null($this->payload) && $this->type == self::TYPE_RESPONSE && $response === $this->payload;
    }
}
