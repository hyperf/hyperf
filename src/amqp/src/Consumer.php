<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Amqp;

use Hyperf\Amqp\Exception\MessageException;
use Hyperf\Amqp\Message\ConsumerMessageInterface;
use Hyperf\Amqp\Message\MessageInterface;
use Hyperf\Amqp\Pool\PoolFactory;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class Consumer extends Builder
{
    /**
     * @var bool
     */
    protected $status = true;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ContainerInterface $container,
        PoolFactory $poolFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($container, $poolFactory);
        $this->logger = $logger;
    }

    public function consume(ConsumerMessageInterface $consumerMessage): void
    {
        $pool = $this->getConnectionPool($consumerMessage->getPoolName());
        /** @var \Hyperf\Amqp\Connection $connection */
        $connection = $pool->get();
        $channel = $connection->getConfirmChannel();

        $this->declare($consumerMessage, $channel);

        $channel->basic_consume(
            $consumerMessage->getQueue(),
            $consumerMessage->getRoutingKey(),
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($consumerMessage) {
                $data = $consumerMessage->unserialize($message->getBody());
                /** @var AMQPChannel $channel */
                $channel = $message->delivery_info['channel'];
                $deliveryTag = $message->delivery_info['delivery_tag'];
                try {
                    $result = $consumerMessage->consume($data);
                    if ($result === Result::ACK) {
                        $this->logger->debug($deliveryTag . ' acked.');
                        return $channel->basic_ack($deliveryTag);
                    }
                    if ($consumerMessage->isRequeue() && $result === Result::REQUEUE) {
                        $this->logger->debug($deliveryTag . ' requeued.');
                        return $channel->basic_reject($deliveryTag, true);
                    }
                } catch (Throwable $exception) {
                    $this->logger->debug($exception->getMessage());
                }
                $this->logger->debug($deliveryTag . ' rejected.');
                $channel->basic_reject($deliveryTag, false);
            }
        );

        while (count($channel->callbacks) > 0) {
            $channel->wait();
        }

        $pool->release($connection);
    }

    public function declare(MessageInterface $message, ?AMQPChannel $channel = null): void
    {
        if (! $message instanceof ConsumerMessageInterface) {
            throw new MessageException('Message must instanceof ' . ConsumerMessageInterface::class);
        }

        if (! $channel) {
            $pool = $this->getConnectionPool($message->getPoolName());
            /** @var \Hyperf\Amqp\Connection $connection */
            $connection = $pool->get();
            $channel = $connection->getChannel();
        }

        parent::declare($message, $channel);

        $builder = $message->getQueueBuilder();

        $channel->queue_declare($builder->getQueue(), $builder->isPassive(), $builder->isDurable(), $builder->isExclusive(), $builder->isAutoDelete(), $builder->isNowait(), $builder->getArguments(), $builder->getTicket());

        $routineKeys = explode(',', $message->getRoutingKey());
        foreach ($routineKeys as $v) {
            if (! empty($v)) {
                $channel->queue_bind($message->getQueue(), $message->getExchange(), $v);
            }
        }
    }
}
