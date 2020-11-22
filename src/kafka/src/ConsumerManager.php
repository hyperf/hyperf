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
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Kafka\Annotation\Consumer as ConsumerAnnotation;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\ProcessManager;
use longlang\phpkafka\Client\SwooleClient;
use longlang\phpkafka\Consumer\ConsumeMessage;
use longlang\phpkafka\Consumer\Consumer as LongLangConsumer;
use longlang\phpkafka\Consumer\ConsumerConfig;
use longlang\phpkafka\Socket\SwooleSocket;
use Psr\Container\ContainerInterface;

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
            if (! $instance instanceof AbstractConsumer) {
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

            public function __construct(ContainerInterface $container, AbstractConsumer $consumer)
            {
                parent::__construct($container);
                $this->consumer = $consumer;
                $this->config = $container->get(ConfigInterface::class);
            }

            public function handle(): void
            {
                $config = $this->config->get('kafka.' . $this->consumer->getPool());
                $consumerConfig = new ConsumerConfig();
                $consumerConfig->setAutoCommit($this->consumer->isAutoCommit());
                $consumerConfig->setRackId($config['rack_id']);
                $consumerConfig->setReplicaId($config['replica_id']);
                $consumerConfig->setTopic($this->consumer->getTopic());
                $consumerConfig->setRebalanceTimeout($config['rebalance_timeout']);
                $consumerConfig->setSendTimeout($config['send_timeout']);
                $groupId = ! empty($this->consumer->getGroupId()) ? sprintf('%s-%s', $this->consumer->getGroupId(), uniqid('')) : '';
                $consumerConfig->setGroupId($groupId);
                $consumerConfig->setGroupInstanceId($groupId ?: sprintf('%s-%s', $groupId ?: '', uniqid('')));
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

                $consumer = new LongLangConsumer(
                    $consumerConfig,
                    function (ConsumeMessage $message) {
                        $this->consumer->consume($message);
                    }
                );
                $consumer->start();
            }
        };
    }
}
