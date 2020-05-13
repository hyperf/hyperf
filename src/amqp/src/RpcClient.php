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
namespace Hyperf\Amqp;

use Hyperf\Amqp\Message\RpcMessageInterface;
use PhpAmqpLib\Message\AMQPMessage;

class RpcClient extends Builder
{
    public function call(RpcMessageInterface $rpcMessage, int $timeout = 5)
    {
        try {
            $pool = $this->poolFactory->getRpcPool($rpcMessage->getPoolName());
            /** @var null|RpcConnection $connection */
            $connection = $pool->get();
            $channel = $connection->initChannel($rpcMessage->getQueueBuilder(), uniqid());

            $message = new AMQPMessage(
                $rpcMessage->serialize(),
                [
                    'correlation_id' => $connection->getCorrelationId(),
                    'reply_to' => $connection->getQueue(),
                ]
            );

            $channel->basic_publish($message, $rpcMessage->getExchange(), $rpcMessage->getRoutingKey());
            $body = $connection->getAMQPMessage($timeout)->getBody();
            return $rpcMessage->unserialize($body);
        } finally {
            $connection && $connection->release();
        }
    }
}
