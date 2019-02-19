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

use PhpAmqpLib\Message\AMQPMessage;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Amqp\Message\ProducerMessageInterface;

class Producer extends Builder
{
    public function produce(ProducerMessageInterface $producerMessage, bool $confirm = false, int $timeout = 5): bool
    {
        $result = false;

        $this->injectMessageProperty($producerMessage);

        $message = new AMQPMessage($producerMessage->payload(), $producerMessage->getProperties());
        $pool = $this->getConnectionPool($producerMessage->getPoolName());
        /** @var \Hyperf\Amqp\Connection $connection */
        $connection = $pool->get();
        if ($confirm) {
            $channel = $connection->getConfirmChannel();
        } else {
            $channel = $connection->getChannel();
        }
        $channel->set_ack_handler(function () use (&$result) {
            $result = true;
        });
        $channel->basic_publish($message, $producerMessage->getExchange(), $producerMessage->getRoutingKey());
        $channel->wait_for_pending_acks_returns($timeout);
        $connection->release();

        return $confirm ? $result : true;
    }

    private function injectMessageProperty(ProducerMessageInterface $producerMessage)
    {
        /** @var \Hyperf\Amqp\Annotation\Producer $annotation */
        $annotation = AnnotationCollector::getClassAnnotation(get_class($producerMessage), Annotation\Producer::class);
        $annotation->routingKey && $producerMessage->setRoutingKey($annotation->routingKey);
        $annotation->exchange && $producerMessage->setExchange($annotation->exchange);
    }
}
