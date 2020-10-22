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
