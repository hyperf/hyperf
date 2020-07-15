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

use Hyperf\Amqp\Event\AfterConsume;
use Hyperf\Amqp\Event\BeforeConsume;
use Hyperf\Amqp\Event\FailToConsume;
use Hyperf\Amqp\Exception\MaxConsumptionException;
use Hyperf\Amqp\Exception\MessageException;
use Hyperf\Amqp\Message\ConsumerMessageInterface;
use Hyperf\Amqp\Message\MessageInterface;
use Hyperf\Amqp\Pool\PoolFactory;
use Hyperf\Contract\ConfigInterface;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Utils\Coroutine\Concurrent;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class Consumer extends Builder
{
    /**
     * @var bool
     */
    protected $status = true;

    /**
     * @var null|EventDispatcherInterface
     */
    protected $eventDispatcher;

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
        if ($container->has(EventDispatcherInterface::class)) {
            $this->eventDispatcher = $container->get(EventDispatcherInterface::class);
        }
    }

    public function consume(ConsumerMessageInterface $consumerMessage): void
    {
        $pool = $this->getConnectionPool($consumerMessage->getPoolName());
        /** @var \Hyperf\Amqp\Connection $connection */
        $connection = $pool->get();
        $channel = $connection->getConfirmChannel();

        $this->declare($consumerMessage, $channel);
        $concurrent = $this->getConcurrent($consumerMessage->getPoolName());

        $maxConsumption = $consumerMessage->getMaxConsumption();
        $currentConsumption = 0;

        $channel->basic_consume(
            $consumerMessage->getQueue(),
            $consumerMessage->getConsumerTag(),
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($consumerMessage, $concurrent, $maxConsumption, &$currentConsumption) {
                if ($maxConsumption > 0 && $currentConsumption++ >= $maxConsumption) {
                    throw new MaxConsumptionException();
                }
                $callback = $this->getCallback($consumerMessage, $message);
                if (! $concurrent instanceof Concurrent) {
                    return parallel([$callback]);
                }

                $concurrent->create($callback);
            }
        );

        try {
            while ($channel->is_consuming()) {
                $channel->wait();
            }
        } catch (MaxConsumptionException $ex) {
        }

        $pool->release($connection);
    }

    public function declare(MessageInterface $message, ?AMQPChannel $channel = null, bool $release = false): void
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

        $routineKeys = (array) $message->getRoutingKey();
        foreach ($routineKeys as $routingKey) {
            $channel->queue_bind($message->getQueue(), $message->getExchange(), $routingKey);
        }

        if (is_array($qos = $message->getQos())) {
            $size = $qos['prefetch_size'] ?? null;
            $count = $qos['prefetch_count'] ?? null;
            $global = $qos['global'] ?? null;
            $channel->basic_qos($size, $count, $global);
        }

        if (isset($connection) && $release) {
            $connection->release();
        }
    }

    protected function getConcurrent(string $pool): ?Concurrent
    {
        $config = $this->container->get(ConfigInterface::class);
        $concurrent = (int) $config->get('amqp.' . $pool . '.concurrent.limit', 0);
        if ($concurrent > 1) {
            return new Concurrent($concurrent);
        }

        return null;
    }

    protected function getCallback(ConsumerMessageInterface $consumerMessage, AMQPMessage $message)
    {
        return function () use ($consumerMessage, $message) {
            $data = $consumerMessage->unserialize($message->getBody());
            /** @var AMQPChannel $channel */
            $channel = $message->delivery_info['channel'];
            $deliveryTag = $message->delivery_info['delivery_tag'];

            try {
                $this->eventDispatcher && $this->eventDispatcher->dispatch(new BeforeConsume($consumerMessage));
                $result = $consumerMessage->consumeMessage($data, $message);
                $this->eventDispatcher && $this->eventDispatcher->dispatch(new AfterConsume($consumerMessage, $result));
            } catch (Throwable $exception) {
                $this->eventDispatcher && $this->eventDispatcher->dispatch(new FailToConsume($consumerMessage, $exception));
                if ($this->container->has(FormatterInterface::class)) {
                    $formatter = $this->container->get(FormatterInterface::class);
                    $this->logger->error($formatter->format($exception));
                } else {
                    $this->logger->error($exception->getMessage());
                }

                $result = Result::DROP;
            }

            if ($result === Result::ACK) {
                $this->logger->debug($deliveryTag . ' acked.');
                return $channel->basic_ack($deliveryTag);
            }
            if ($result === Result::NACK) {
                $this->logger->debug($deliveryTag . ' uacked.');
                return $channel->basic_nack($deliveryTag);
            }
            if ($consumerMessage->isRequeue() && $result === Result::REQUEUE) {
                $this->logger->debug($deliveryTag . ' requeued.');
                return $channel->basic_reject($deliveryTag, true);
            }

            $this->logger->debug($deliveryTag . ' rejected.');
            return $channel->basic_reject($deliveryTag, false);
        };
    }
}
