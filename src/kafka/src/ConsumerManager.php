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
namespace Hyperf\Kafka;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Kafka\Annotation\Consumer as ConsumerAnnotation;
use Hyperf\Kafka\Event\AfterConsume;
use Hyperf\Kafka\Event\BeforeConsume;
use Hyperf\Kafka\Event\FailToConsume;
use Hyperf\Kafka\Exception\InvalidConsumeResultException;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;
use longlang\phpkafka\Client\SwooleClient;
use longlang\phpkafka\Consumer\ConsumeMessage;
use longlang\phpkafka\Consumer\Consumer as LongLangConsumer;
use longlang\phpkafka\Consumer\ConsumerConfig;
use longlang\phpkafka\Socket\SwooleSocket;
use longlang\phpkafka\Timer\SwooleTimer;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

use function Hyperf\Coroutine\wait;
use function Hyperf\Support\make;

class ConsumerManager
{
    protected LongLangConsumer $consumer;

    public function __construct(private ContainerInterface $container)
    {
    }

    public function run()
    {
        $classes = AnnotationCollector::getClassesByAnnotation(ConsumerAnnotation::class);

        /**
         * @var string $class
         * @var ConsumerAnnotation $annotation
         */
        foreach ($classes as $class => $annotation) {
            $instance = make($class);

            if (! $instance instanceof AbstractConsumer || ! $instance->isEnable($annotation->enable)) {
                continue;
            }

            $annotation->pool && $instance->setPool($annotation->pool);
            $annotation->topic && $instance->setTopic($annotation->topic);
            $annotation->groupId && $instance->setGroupId($annotation->groupId);
            $annotation->memberId && $instance->setMemberId($annotation->memberId);
            $instance->setAutoCommit($annotation->autoCommit);

            $process = $this->createProcess($instance);
            $process->name = $instance->getName();
            $process->nums = (int) $annotation->nums;
            ProcessManager::register($process);
        }
    }

    protected function createProcess(AbstractConsumer $consumer): AbstractProcess
    {
        return new class($this->container, $consumer) extends AbstractProcess {
            private AbstractConsumer $consumer;

            private ConfigInterface $config;

            private ?EventDispatcherInterface $dispatcher;

            protected StdoutLoggerInterface $stdoutLogger;

            protected Producer $producer;

            public function __construct(ContainerInterface $container, AbstractConsumer $consumer)
            {
                parent::__construct($container);
                $this->consumer = $consumer;
                $this->config = $container->get(ConfigInterface::class);
                $this->stdoutLogger = $container->get(StdoutLoggerInterface::class);
                $this->producer = $container->get(Producer::class);
                if ($container->has(EventDispatcherInterface::class)) {
                    $this->dispatcher = $container->get(EventDispatcherInterface::class);
                }
            }

            public function isEnable($server): bool
            {
                return $this->config->get(sprintf('kafka.%s.enable', $this->consumer->getPool()), true);
            }

            public function handle(): void
            {
                $consumerConfig = $this->getConsumerConfig();
                $consumer = $this->consumer;
                $longLangConsumer = new LongLangConsumer(
                    $consumerConfig,
                    function (ConsumeMessage $message) use ($consumer, $consumerConfig) {
                        $config = $this->getConfig();
                        wait(function () use ($consumer, $consumerConfig, $message) {
                            $this->dispatcher?->dispatch(new BeforeConsume($consumer, $message));

                            $result = $consumer->consume($message);

                            if (! $consumerConfig->getAutoCommit()) {
                                if (! is_string($result)) {
                                    throw new InvalidConsumeResultException('The result is invalid.');
                                }

                                if ($result === Result::ACK) {
                                    $message->getConsumer()->ack($message);
                                }

                                if ($result === Result::REQUEUE) {
                                    $this->producer->send($message->getTopic(), $message->getValue(), $message->getKey(), $message->getHeaders());
                                }
                            }

                            $this->dispatcher?->dispatch(new AfterConsume($consumer, $message, $result));
                        }, $config['consume_timeout'] ?? -1);
                    }
                );

                // stop consumer when worker exit
                Coroutine::create(function () use ($longLangConsumer) {
                    CoordinatorManager::until(Constants::WORKER_EXIT)->yield();
                    $longLangConsumer->stop();
                });

                while (true) {
                    try {
                        $longLangConsumer->start();
                    } catch (Throwable $exception) {
                        $this->stdoutLogger->warning((string) $exception);
                        $this->dispatcher?->dispatch(new FailToConsume($this->consumer, [], $exception));
                    }

                    if (CoordinatorManager::until(Constants::WORKER_EXIT)->yield(10)) {
                        break;
                    }
                }

                $longLangConsumer->close();
            }

            public function getConfig(): array
            {
                return $this->config->get('kafka.' . $this->consumer->getPool());
            }

            public function getConsumerConfig(): ConsumerConfig
            {
                $config = $this->getConfig();
                $consumerConfig = new ConsumerConfig();
                $consumerConfig->setAutoCommit($this->consumer->isAutoCommit());
                $consumerConfig->setRackId($config['rack_id']);
                $consumerConfig->setReplicaId($config['replica_id']);
                $consumerConfig->setTopic($this->consumer->getTopic());
                $consumerConfig->setRebalanceTimeout($config['rebalance_timeout']);
                $consumerConfig->setSendTimeout($config['send_timeout']);
                $consumerConfig->setGroupId($this->consumer->getGroupId() ?? uniqid('hyperf-kafka-'));
                $consumerConfig->setGroupInstanceId(sprintf('%s-%s', $this->consumer->getGroupId(), uniqid()));
                $consumerConfig->setMemberId($this->consumer->getMemberId() ?: '');
                $consumerConfig->setInterval($config['interval']);
                $consumerConfig->setBootstrapServers($config['bootstrap_servers']);
                $consumerConfig->setClient($config['client'] ?? SwooleClient::class);
                $consumerConfig->setSocket($config['socket'] ?? SwooleSocket::class);
                $consumerConfig->setTimer($config['timer'] ?? SwooleTimer::class);
                $consumerConfig->setMaxWriteAttempts($config['max_write_attempts']);
                $consumerConfig->setClientId(sprintf('%s-%s', $config['client_id'] ?: 'Hyperf', uniqid()));
                $consumerConfig->setRecvTimeout($config['recv_timeout']);
                $consumerConfig->setConnectTimeout($config['connect_timeout']);
                $consumerConfig->setSessionTimeout($config['session_timeout']);
                $consumerConfig->setGroupRetry($config['group_retry']);
                $consumerConfig->setGroupRetrySleep($config['group_retry_sleep']);
                $consumerConfig->setGroupHeartbeat($config['group_heartbeat']);
                $consumerConfig->setOffsetRetry($config['offset_retry']);
                $consumerConfig->setAutoCreateTopic($config['auto_create_topic']);
                $consumerConfig->setPartitionAssignmentStrategy($config['partition_assignment_strategy']);
                ! empty($config['sasl']) && $consumerConfig->setSasl($config['sasl']);
                ! empty($config['ssl']) && $consumerConfig->setSsl($config['ssl']);
                return $consumerConfig;
            }
        };
    }
}
