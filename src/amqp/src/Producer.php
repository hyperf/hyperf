<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Amqp;

use Hyperf\Amqp\Message\ProducerMessageInterface;
use PhpAmqpLib\Message\AMQPMessage;

class Producer extends Builder
{

    public function produce(ProducerMessageInterface $producerMessage, int $timeout = 5): bool
    {
        $result = false;

        $message = new AMQPMessage($producerMessage->payload(), $producerMessage->getProperties());
        $channelPool = $this->getChannelPool($producerMessage->getPoolName());
        /** @var \PhpAmqpLib\Channel\AMQPChannel $channel */
        $channel = $channelPool->get();
        var_dump(spl_object_id($channel));
        $channel->confirm_select(true);
        $channel->set_ack_handler(function () use (&$result) {
            $result = true;
        });
        $channel->basic_publish($message, $producerMessage->getExchange(), $producerMessage->getRoutingKey());
        $channel->wait_for_pending_acks_returns();
        $channelPool->release($channel);

        return $result;
    }

}
