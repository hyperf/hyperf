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
use longlang\phpkafka\Exception\KafkaErrorException;
use longlang\phpkafka\Producer\ProduceMessage;
use longlang\phpkafka\Producer\Producer as LongLangProducer;
use longlang\phpkafka\Producer\ProducerConfig;
use longlang\phpkafka\Protocol\CreateTopics\CreatableTopic;
use longlang\phpkafka\Protocol\CreateTopics\CreateTopicsRequest;
use longlang\phpkafka\Protocol\ErrorCode;
use longlang\phpkafka\Socket\SwooleSocket;
use Psr\Container\ContainerInterface;

/**
 * @method close()
 * @method getConfig()
 * @method getBroker()
 */
class Producer
{
    /**
     * @var LongLangProducer
     */
    protected $producer;

    /**
     * @var array
     */
    protected $config;

    public function __construct(ContainerInterface $container, string $name = 'default')
    {
        $config = $container->get(ConfigInterface::class)->get('kafka.' . $name);
        $this->config = $config;
        $producerConfig = new ProducerConfig();
        $producerConfig->setConnectTimeout($config['connect_timeout']);
        $producerConfig->setSendTimeout($config['send_timeout']);
        $producerConfig->setRecvTimeout($config['recv_timeout']);
        $producerConfig->setClientId($config['client_id']);
        $producerConfig->setMaxWriteAttempts($config['max_write_attempts']);
        $producerConfig->setSocket(SwooleSocket::class);
        $producerConfig->setBrokers($config['brokers']);
        $producerConfig->setBootstrapServer($config['bootstrap_server']);
        $producerConfig->setUpdateBrokers($config['update_brokers']);
        $producerConfig->setAcks($config['acks']);
        $producerConfig->setProducerId($config['producer_id']);
        $producerConfig->setProducerEpoch($config['producer_epoch']);
        $producerConfig->setPartitionLeaderEpoch($config['partition_leader_epoch']);

        $this->producer = new LongLangProducer($producerConfig);
    }

    public function __call($name, $arguments)
    {
        return $this->producer->{$name}(...$arguments);
    }

    public function send(string $topic, ?string $value, ?string $key = null, array $headers = [], int $partitionIndex = 0, ?int $brokerId = null)
    {
        try {
            $this->producer->send($topic, $value, $key, $headers, $partitionIndex, $brokerId);
        } catch (KafkaErrorException $e) {
            switch ($e->getCode()) {
                case ErrorCode::UNKNOWN_TOPIC_OR_PARTITION:
                    if (! $this->config['is_auto_create_topic']) {
                        throw $e;
                    }
                    $this->createTopics($topic);
                    $this->send($topic, $value, $key, $headers, $partitionIndex, $brokerId);
            }
        }
    }

    /**
     * @param ProduceMessage[] $messages
     */
    public function sendBatch(array $messages, ?int $brokerId = null)
    {
        try {
            $this->producer->sendBatch($messages, $brokerId);
        } catch (KafkaErrorException $e) {
            switch ($e->getCode()) {
                case ErrorCode::UNKNOWN_TOPIC_OR_PARTITION:
                    if (! $this->config['is_auto_create_topic']) {
                        throw $e;
                    }
                    $this->createTopics(null, $messages);
                    $this->producer->sendBatch($messages, $brokerId);
            }
        }
    }

    protected function createTopics(?string $topic = null, ?array $messages = null)
    {
        $createTopicsRequest = new CreateTopicsRequest();
        $topics = [];
        if (! empty($topic)) {
            $topics[] = (new CreatableTopic())->setName($topic)
                ->setNumPartitions($this->config['num_partitions'] ?? 1)
                ->setReplicationFactor($this->config['replication_factor'] ?? 3);
        }

        if (! empty($messages)) {
            /** @var ProduceMessage $message */
            foreach ($messages as $message) {
                $topics[] = (new CreatableTopic())->setName($message->getTopic())
                    ->setNumPartitions($this->config['num_partitions'] ?? 50)
                    ->setReplicationFactor($this->config['replication_factor'] ?? 50);
            }
        }

        $createTopicsRequest->setTopics($topics);
        $createTopicsRequest->setValidateOnly(false);
        $this->producer->getBroker()->getClient()->send($createTopicsRequest);
    }
}
