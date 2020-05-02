<?php

namespace Hyperf\Amqp\Message;


class DynamicRpcMessage extends RpcMessage
{

    public function __construct(string $exchange, string $routingKey, $data)
    {
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
        $this->payload = $data;
    }

}