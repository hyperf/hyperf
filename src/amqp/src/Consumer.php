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
use Hyperf\Amqp\Event\WaitTimeout;
use Hyperf\Amqp\Exception\MessageException;
use Hyperf\Amqp\Message\ConsumerMessageInterface;
use Hyperf\Amqp\Message\MessageInterface;
use Hyperf\Amqp\Message\Type;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Coroutine\Concurrent;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\Process\ProcessManager;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Throwable;

use function Hyperf\Coroutine\parallel;

class Consumer extends Builder
{
    protected ?EventDispatcherInterface $eventDispatcher = null;

    public function __construct(
        ContainerInterface $container,
        ConnectionFactory $factory,
        private LoggerInterface $logger
    ) {
        parent::__construct($container, $factory);
        if ($container->has(EventDispatcherInterface::class)) {
            $this->eventDispatcher = $container->get(EventDispatcherInterface::class);
        }
    }

    public function consume(ConsumerMessageInterface $consumerMessage): void
    {
        $connection = $this->factory->getConnection($consumerMessage->getPoolName());

        try {
            $channel = $connection->getConfirmChannel();
            $exchange = $consumerMessage->getExchange();

            try {
                DeclaredExchanges::add($exchange);
                $this->declare($consumerMessage, $channel);
            } catch (Throwable $exception) {
                DeclaredExchanges::remove($exchange);
                throw $exception;
            }

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
                function (AMQPMessage $message) use ($consumerMessage, $concurrent) {
                    $callback = $this->getCallback($consumerMessage, $message);
                    if (! $concurrent instanceof Concurrent) {
                        return parallel([$callback]);
                    }

                    $concurrent->create($callback);
                }
            );

            while ($channel->is_consuming() && ProcessManager::isRunning()) {
                try {
                    $channel->wait(null, false, $consumerMessage->getWaitTimeout());
                    if ($maxConsumption > 0 && ++$currentConsumption >= $maxConsumption) {
                        break;
                    }
                } catch (AMQPTimeoutException) {
                    $this->eventDispatcher?->dispatch(new WaitTimeout($consumerMessage));
                } catch (Throwable $exception) {
                    $this->logger->error((string) $exception);
                    break;
                }
            }
        } catch (Throwable $exception) {
            isset($channel) && $channel->close();
            throw $exception;
        }

        $this->waitConcurrentHandled($concurrent);

        $connection->releaseChannel($channel, true);
    }

    public function declare(MessageInterface $message, ?AMQPChannel $channel = null, bool $release = false): void
    {
        if (! $message instanceof ConsumerMessageInterface) {
            throw new MessageException('Message must instanceof ' . ConsumerMessageInterface::class);
        }

        if (! $channel) {
            $connection = $this->factory->getConnection($message->getPoolName());
            $channel = $connection->getChannel();
        }

        parent::declare($message, $channel);

        $builder = $message->getQueueBuilder();

        $channel->queue_declare($builder->getQueue(), $builder->isPassive(), $builder->isDurable(), $builder->isExclusive(), $builder->isAutoDelete(), $builder->isNowait(), $builder->getArguments(), $builder->getTicket());

        $routineKeys = (array) $message->getRoutingKey();
        foreach ($routineKeys as $routingKey) {
            $channel->queue_bind($message->getQueue(), $message->getExchange(), $routingKey);
        }

        if (empty($routineKeys) && $message->getType() === Type::FANOUT) {
            $channel->queue_bind($message->getQueue(), $message->getExchange());
        }

        if (is_array($qos = $message->getQos())) {
            $size = $qos['prefetch_size'] ?? null;
            $count = $qos['prefetch_count'] ?? null;
            $global = $qos['global'] ?? null;
            $channel->basic_qos($size, $count, $global);
        }
    }

    /**
     * Wait the tasks in concurrent handled, the max wait time is 5s.
     * @param int $interval The wait interval ms
     * @param int $count The wait count
     */
    protected function waitConcurrentHandled(?Concurrent $concurrent, int $interval = 10, int $count = 500): void
    {
        $index = 0;
        while ($concurrent && ! $concurrent->isEmpty()) {
            usleep($interval * 1000);
            if ($index++ > $count) {
                break;
            }
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

    protected function getCallback(ConsumerMessageInterface $consumerMessage, AMQPMessage $message): callable
    {
        return function () use ($consumerMessage, $message) {
            $channel = $message->getChannel();
            $deliveryTag = $message->getDeliveryTag();

            try {
                $data = $consumerMessage->unserialize($message->getBody());

                $this->eventDispatcher?->dispatch(new BeforeConsume($consumerMessage, $message));
                $result = $consumerMessage->consumeMessage($data, $message);
                $this->eventDispatcher?->dispatch(new AfterConsume($consumerMessage, $result, $message));
            } catch (Throwable $exception) {
                $this->eventDispatcher?->dispatch(new FailToConsume($consumerMessage, $exception, $message));
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
                $channel->basic_ack($deliveryTag);
                return;
            }
            if ($result === Result::NACK) {
                $this->logger->debug($deliveryTag . ' nacked.');
                $channel->basic_nack($deliveryTag, false, $consumerMessage->isRequeue());
                return;
            }
            if ($consumerMessage->isRequeue() && $result === Result::REQUEUE) {
                $this->logger->debug($deliveryTag . ' requeued.');
                $channel->basic_reject($deliveryTag, true);
                return;
            }

            $this->logger->debug($deliveryTag . ' rejected.');
            $channel->basic_reject($deliveryTag, false);
        };
    }
}
