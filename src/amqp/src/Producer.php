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
namespace Hyperf\Amqp;

use Hyperf\Amqp\Message\ProducerMessageInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

use function Hyperf\Support\retry;

class Producer extends Builder
{
    public function produce(ProducerMessageInterface $producerMessage, bool $confirm = false, int $timeout = 5): bool
    {
        return retry(1, fn () => $this->produceMessage($producerMessage, $confirm, $timeout));
    }

    private function produceMessage(ProducerMessageInterface $producerMessage, bool $confirm = false, int $timeout = 5)
    {
        $result = false;

        $this->injectMessageProperty($producerMessage);

        $message = new AMQPMessage($producerMessage->payload(), $producerMessage->getProperties());
        $connection = $this->factory->getConnection($producerMessage->getPoolName());

        try {
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
        } catch (Throwable $exception) {
            isset($channel) && $channel->close();
            throw $exception;
        }

        if ($confirm) {
            $connection->releaseChannel($channel, true);
        } else {
            $result = true;
            $connection->releaseChannel($channel);
        }

        return $result;
    }

    private function injectMessageProperty(ProducerMessageInterface $producerMessage)
    {
        if (class_exists(AnnotationCollector::class)) {
            /** @var null|\Hyperf\Amqp\Annotation\Producer $annotation */
            $annotation = AnnotationCollector::getClassAnnotation(get_class($producerMessage), Annotation\Producer::class);
            if ($annotation) {
                $annotation->routingKey && $producerMessage->setRoutingKey($annotation->routingKey);
                $annotation->exchange && $producerMessage->setExchange($annotation->exchange);
            }
        }
    }
}
