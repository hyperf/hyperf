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

    public function produce(ProducerMessageInterface $producerMessage, int $timeout = 5): bool
    {
        $result = false;

        $this->injectMessageProperty($producerMessage);
        
        $message = new AMQPMessage($producerMessage->payload(), $producerMessage->getProperties());
        $pool = $this->getConnectionPool($producerMessage->getPoolName());
        /** @var \PhpAmqpLib\Connection\AbstractConnection $connection */
        $connection = $pool->get();
        /** @var \PhpAmqpLib\Channel\AMQPChannel $channel */
        $needConfirmSelect = false;
        if (isset($connection->channels[1])) {
            $needConfirmSelect = true;
        }
        $channel = $connection->channel(1);
        $needConfirmSelect && $channel->confirm_select(false);
        $channel->set_ack_handler(function () use (&$result) {
            $result = true;
        });
        $channel->basic_publish($message, $producerMessage->getExchange(), $producerMessage->getRoutingKey());
        $channel->wait_for_pending_acks_returns($timeout);
        $pool->release($connection);

        return $result;
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
