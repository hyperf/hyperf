<?php

namespace Hyperf\Nsq;


use Swoole\Coroutine\Socket;

class Nsq
{

    /**
     * @var \Swoole\Coroutine\Socket
     */
    protected $socket;

    public function publish($topic, $message)
    {
        $builder = new MessageBuilder(new Packer());
        $payload = $builder->buildPub($topic, $message);
        $this->socket->send($payload);
    }

    public function subscribe(string $topic, string $channel, callable $callback)
    {
        $builder = new MessageBuilder(new Packer());
        $this->socket->send($builder->buildSub($topic, $channel));
        $this->socket->recv();
        $this->socket->send($builder->buildRdy(1));
        while ($this->socket->send($builder->buildRdy(1))) {
            $reader = new Subscriber($this->socket, new Packer());
            $reader->recv();

            if ($reader->isMessage()) {
                if ($reader->isHeartbeat()) {
                    var_dump('heartbeat');
                    $this->socket->send("NOP\n");
                } else {
                    $message = $reader->getMessage();
                    try {
                        $callback($message);
                    } catch (\Throwable $throwable) {
                        $this->socket->send($builder->buildTouch($message->getMessageId()));
                        $this->socket->send($builder->buildReq($message->getMessageId()));
                    }
                    $this->socket->send($builder->buildFin($message->getMessageId()));
                }
            }
        }
    }


    public function connect(string $host, int $port)
    {
        $this->socket = new Socket(AF_INET, SOCK_STREAM, 0);
        if (! $this->socket->connect($host, $port)) {
            throw new \RuntimeException('Connect failed');
        }
        $this->socket->send("  V2");
    }

}