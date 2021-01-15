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

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Kafka\Pool\KafkaPool;
use Hyperf\Pool\Connection as BaseConnection;
use longlang\phpkafka\Producer\Producer;
use longlang\phpkafka\Producer\Producer as LongLangConsumer;
use longlang\phpkafka\Producer\ProducerConfig;
use longlang\phpkafka\Socket\SwooleSocket;
use Psr\Container\ContainerInterface;

class KafkaConnection extends BaseConnection implements ConnectionInterface
{
    /**
     * @var KafkaPool
     */
    protected $pool;

    /**
     * @var null|LongLangConsumer
     */
    protected $connection;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Context|mixed
     */
    protected $context;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container, KafkaPool $pool, array $config)
    {
        parent::__construct($container, $pool);
        $this->logger = $container->get(StdoutLoggerInterface::class);
        $this->config = $config;
        $this->context = $container->get(Context::class);
        $this->connection = $this->initConnection();
    }

    public function __call($name, $arguments)
    {
        return $this->connection->{$name}(...$arguments);
    }

    public function getActiveConnection()
    {
        if ($this->check()) {
            return $this->connection;
        }

        $this->reconnect();

        return $this->connection;
    }

    public function check(): bool
    {
        return isset($this->connection) && $this->connection instanceof Producer;
    }

    public function reconnect(): bool
    {
        if ($this->connection) {
            $this->close();
        }

        $this->connection = $this->initConnection();
        return true;
    }

    public function close(): bool
    {
        try {
            $this->connection->close();
        } catch (\Throwable $exception) {
            $this->logger->error((string) $exception);
        } finally {
            $this->connection = null;
        }
        return true;
    }

    protected function initConnection()
    {
        $producerConfig = new ProducerConfig();
        $producerConfig->setConnectTimeout($this->config['connect_timeout']);
        $producerConfig->setSendTimeout($this->config['send_timeout']);
        $producerConfig->setRecvTimeout($this->config['recv_timeout']);
        $producerConfig->setClientId($this->config['client_id']);
        $producerConfig->setMaxWriteAttempts($this->config['max_write_attempts']);
        $producerConfig->setSocket(SwooleSocket::class);
        $producerConfig->setBrokers($this->config['brokers']);
        $producerConfig->setBootstrapServer($this->config['bootstrap_server']);
        $producerConfig->setUpdateBrokers($this->config['update_brokers']);
        $producerConfig->setAcks($this->config['acks']);
        $producerConfig->setProducerId($this->config['producer_id']);
        $producerConfig->setProducerEpoch($this->config['producer_epoch']);
        $producerConfig->setPartitionLeaderEpoch($this->config['partition_leader_epoch']);

        return new LongLangConsumer($producerConfig);
    }
}
