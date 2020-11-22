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
use Hyperf\Crontab\LoggerInterface;
use Hyperf\Kafka\Pool\KafkaConnectionPool;
use Hyperf\Pool\Connection as BaseConnection;
use Hyperf\Utils\Arr;
use longlang\phpkafka\Producer\Producer;
use Psr\Container\ContainerInterface;

class ProducerConnection extends BaseConnection implements ConnectionInterface
{
    /**
     * @var KafkaConnectionPool
     */
    protected $pool;

    /**
     * @var Producer
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

    public function __construct(ContainerInterface $container, KafkaConnectionPool $pool, array $config)
    {
        parent::__construct($container, $pool);
        $this->config = $config;
        $this->context = $container->get(Context::class);
        $this->params = new Params(Arr::get($config, 'params', []));
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
            $this->getLogger()->error((string) $exception);
        } finally {
            $this->connection = null;
        }
    }

    protected function initConnection()
    {
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->container->get(StdoutLoggerInterface::class);
    }
}
