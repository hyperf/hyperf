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
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;
use longlang\phpkafka\Client\SwooleClient;
use longlang\phpkafka\Consumer\ConsumeMessage;
use longlang\phpkafka\Consumer\Consumer as LongLangConsumer;
use longlang\phpkafka\Consumer\ConsumerConfig;
use longlang\phpkafka\Exception\KafkaErrorException;
use longlang\phpkafka\Protocol\CreateTopics\CreatableTopic;
use longlang\phpkafka\Protocol\CreateTopics\CreateTopicsRequest;
use longlang\phpkafka\Protocol\ErrorCode;
use longlang\phpkafka\Protocol\JoinGroup\JoinGroupRequest;
use longlang\phpkafka\Socket\SwooleSocket;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

class ConsumerManager
{
    /**
     * @var LongLangConsumer
     */
    protected $consumer;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
            $annotation->autoCommit && $instance->setAutoCommit($annotation->autoCommit);

            $process = $this->createProcess($instance);
            $process->name = $instance->getName() . '-' . $instance->getTopic();
            $process->nums = (int) $annotation->nums;
            ProcessManager::register($process);
        }
    }

    protected function createProcess(AbstractConsumer $consumer): AbstractProcess
    {
        return new class($this->container, $consumer) extends AbstractProcess {
            /**
             * @var AbstractConsumer
             */
            private $consumer;

            /**
             * @var ConfigInterface
             */
            private $config;

            /**
             * @var null|EventDispatcherInterface
             */
            private $dispatcher;

            /**
             * @var StdoutLoggerInterface
             */
            protected $stdoutLogger;

            public function __construct(ContainerInterface $container, AbstractConsumer $consumer)
            {
                parent::__construct($container);
                $this->consumer = $consumer;
                $this->config = $container->get(ConfigInterface::class);
                $this->stdoutLogger = $container->get(StdoutLoggerInterface::class);
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
                    function (ConsumeMessage $message) use ($consumer) {
                        $this->dispatcher && $this->dispatcher->dispatch(new BeforeConsume($consumer, $message));

                        $result = $consumer->consume($message);

                        $this->dispatcher && $this->dispatcher->dispatch(new AfterConsume($consumer, $message, $result));
                    }
                );

                retry(
                    10,
                    function () use ($longLangConsumer, $consumerConfig) {
                        try {
                            $longLangConsumer->start();
                        } catch (KafkaErrorException $exception) {
                            $this->stdoutLogger->error($exception->getMessage());
                            switch ($exception->getCode()) {
                                case ErrorCode::REBALANCE_IN_PROGRESS:
                                    $joinGroupRequest = new JoinGroupRequest();
                                    $joinGroupRequest->setGroupInstanceId($consumerConfig->getGroupInstanceId());
                                    $joinGroupRequest->setMemberId($consumerConfig->getMemberId());
                                    $joinGroupRequest->setGroupId($consumerConfig->getGroupId());
                                    $longLangConsumer->getBroker()->getClient()->send($joinGroupRequest);
                                    $longLangConsumer->start();
                                    break;
                                case ErrorCode::UNKNOWN_TOPIC_OR_PARTITION:
                                    $this->createTopics($longLangConsumer, $consumerConfig->getTopic());
                                    $longLangConsumer->start();
                                    break;
                            }

                            $this->dispatcher && $this->dispatcher->dispatch(new FailToConsume($this->consumer, [], $exception));
                        }
                    },
                    10
                );


            }

            protected function createTopics(LongLangConsumer $consumer, ?string $topic = null)
            {
                $createTopicsRequest = new CreateTopicsRequest();

                $createTopicsRequest->setTopics([(new CreatableTopic())->setName($topic)->setNumPartitions(1)->setReplicationFactor(1)]);
                $createTopicsRequest->setValidateOnly(false);
                $consumer->getBroker()->getClient()->send($createTopicsRequest);
            }

            protected function getConsumerConfig(): ConsumerConfig
            {
                $config = $this->config->get('kafka.' . $this->consumer->getPool());
                $consumerConfig = new ConsumerConfig();
                $consumerConfig->setAutoCommit($this->consumer->isAutoCommit());
                $consumerConfig->setRackId($config['rack_id']);
                $consumerConfig->setReplicaId($config['replica_id']);
                $consumerConfig->setTopic($this->consumer->getTopic());
                $consumerConfig->setRebalanceTimeout($config['rebalance_timeout']);
                $consumerConfig->setSendTimeout($config['send_timeout']);
                $consumerConfig->setGroupId($this->consumer->getGroupId());
                $consumerConfig->setGroupInstanceId(sprintf('%s-%s', $this->consumer->getGroupId(), uniqid('')));
                $consumerConfig->setMemberId($this->consumer->getMemberId() ?: '');
                $consumerConfig->setInterval($config['interval']);
                $consumerConfig->setBroker($config['bootstrap_server']);
                $consumerConfig->setSocket(SwooleSocket::class);
                $consumerConfig->setClient(SwooleClient::class);
                $consumerConfig->setMaxWriteAttempts($config['max_write_attempts']);
                $consumerConfig->setClientId($config['client_id']);
                $consumerConfig->setRecvTimeout($config['recv_timeout']);
                $consumerConfig->setConnectTimeout($config['connect_timeout']);
                $consumerConfig->setPartitions($config['partitions']);
                $consumerConfig->setSessionTimeout($config['session_timeout']);
                return $consumerConfig;
            }
        };
    }
}
