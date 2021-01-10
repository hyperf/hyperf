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
use longlang\phpkafka\Producer\Producer as LongLangProducer;
use longlang\phpkafka\Producer\ProducerConfig;
use longlang\phpkafka\Socket\SwooleSocket;
use Psr\Container\ContainerInterface;

/**
 * @method send(string $topic, ?string $value, ?string $key = null, array $headers = [], int $partitionIndex = 0, ?int $brokerId = null)
 * @method sendBatch(array $messages, ?int $brokerId = null)
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
        $producerConfig->setAutoCreateTopic($config['auto_create_topic']);

        $this->producer = new LongLangProducer($producerConfig);
    }

    public function __call($name, $arguments)
    {
        return $this->producer->{$name}(...$arguments);
    }
}
