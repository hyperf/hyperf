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
use Hyperf\Di\Annotation\AnnotationCollector;
use PhpAmqpLib\Message\AMQPMessage;

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
        $channel = $connection->getChannel($confirm);
        $channel->set_ack_handler(function () use (&$result) {
            $result = true;
        });
        $channel->basic_publish($message, $producerMessage->getExchange(), $producerMessage->getRoutingKey());
        $channel->wait_for_pending_acks_returns($timeout);
        $pool->release($connection);

        return $confirm ? $result : true;
    }

    private function injectMessageProperty(ProducerMessageInterface $producerMessage)
    {
        $item = AnnotationCollector::getClassAnnotation(get_class($producerMessage), Annotation\Producer::class);
        foreach ($item as $key => $value) {
            $setter = setter($key);
            if (method_exists($producerMessage, $setter)) {
                $producerMessage->$setter($value);
            }
        }
    }

}
