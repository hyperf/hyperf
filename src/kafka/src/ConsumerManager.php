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
use longlang\phpkafka\Exception\KafkaErrorException;
use longlang\phpkafka\Socket\SwooleSocket;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

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
            if (! $instance instanceof AbstractConsumer || ! $annotation->enable) {
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

            public function handle(): void
            {
                $consumerConfig = $this->getConsumerConfig();
                $consumer = $this->consumer;
                $longLangConsumer = new LongLangConsumer(
                    $consumerConfig,
                    function (ConsumeMessage $message) use ($consumer, $consumerConfig) {
                        $this->dispatcher && $this->dispatcher->dispatch(new BeforeConsume($consumer, $message));

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

                        $this->dispatcher && $this->dispatcher->dispatch(new AfterConsume($consumer, $message, $result));
                    }
                );

                retry(
                    3,
                    function () use ($longLangConsumer) {
                        try {
                            $longLangConsumer->start();
                        } catch (KafkaErrorException $exception) {
                            $this->stdoutLogger->error($exception->getMessage());

                            $this->dispatcher && $this->dispatcher->dispatch(new FailToConsume($this->consumer, [], $exception));
                        }
                    },
                    10
                );
            }

            public function getConsumerConfig(): ConsumerConfig
            {
                $config = $this->config->get('kafka.' . $this->consumer->getPool());
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
                $consumerConfig->setSocket(SwooleSocket::class);
                $consumerConfig->setClient(SwooleClient::class);
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
