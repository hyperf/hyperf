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
namespace Hyperf\Kafka\Pool;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Kafka\KafkaConnection;
use Hyperf\Pool\Pool;
use Hyperf\Utils\Arr;
use Psr\Container\ContainerInterface;

class KafkaPool extends Pool
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $config;

    public function __construct(ContainerInterface $container, string $name)
    {
        $this->name = $name;
        $config = $container->get(ConfigInterface::class);
        $key = sprintf('kafka.%s', $this->name);
        if (! $config->has($key)) {
            throw new \InvalidArgumentException(sprintf('config[%s] is not exist!', $key));
        }
        $this->config = Arr::merge($this->getDefaultConfig(), $config->get($key, []));
        $options = Arr::get($this->config, 'pool', []);
        parent::__construct($container, $options);
    }

    public function getName(): string
    {
        return $this->name;
    }

    protected function createConnection(): ConnectionInterface
    {
        return new KafkaConnection($this->container, $this, $this->config);
    }

    protected function getDefaultConfig(): array
    {
        return [
            'connect_timeout' => -1,
            'send_timeout' => -1,
            'recv_timeout' => -1,
            'client_id' => '',
            'max_write_attempts' => 3,
            'brokers' => [
                '127.0.0.1:9092',
            ],
            'bootstrap_server' => '127.0.0.1:9092',
            'update_brokers' => true,
            'acks' => 0,
            'producer_id' => -1,
            'producer_epoch' => -1,
            'partition_leader_epoch' => -1,
            'interval' => 0,
            'session_timeout' => 60,
            'rebalance_timeout' => 60,
            'partitions' => [0],
            'replica_id' => -1,
            'rack_id' => '',
            'is_auto_create_topic' => true,
            'pool' => [
                'min_connections' => 1,
                'max_connections' => 10,
                'connect_timeout' => 10.0,
                'wait_timeout' => 3.0,
                'heartbeat' => -1,
                'max_idle_time' => 60.0,
            ],
        ];
    }
}
