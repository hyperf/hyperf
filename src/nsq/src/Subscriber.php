<?php

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

    /**
     * @var \Hyperf\Nsq\Packer
     */
    protected $packer;

    public function __construct(Socket $socket = null, Packer $packer)
    {
        $this->socket = $socket;
        $this->packer = $packer;
    }

    public function recv()
    {
        $data = $this->socket->recv(8);
        $this->size = unpack("N", substr($data, 0, 4))[1];
        $this->type = unpack("N", substr($data, 4, 4))[1];
        $data = $this->socket->recv($this->size - 4);
        $this->payload = $this->readString($data);
        return $this;
    }

    public function getMessage(): \Hyperf\Nsq\Message
    {
        return new \Hyperf\Nsq\Message($this->payload, $this->size);
    }

    public function isMessage()
    {
        return self::TYPE_MESSAGE == $this->type;
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
        return ! is_null($this->payload) && self::TYPE_RESPONSE == $this->type && $response === $this->payload;
    }

    private function readString($content)
    {
        $size = strlen($content);
        $bytes = unpack("c{$size}chars", $content);

        return implode(array_map("chr", $bytes));
    }
}
