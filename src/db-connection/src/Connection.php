<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DbConnection;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Database\ConnectionInterface as DbConnectionInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\DbConnection\Pool\DbPool;
use Hyperf\DbConnection\Traits\DbConnection;
use Hyperf\Pool\Connection as BaseConnection;
use Hyperf\Pool\Exception\ConnectionException;
use Psr\Container\ContainerInterface;

class Connection extends BaseConnection implements ConnectionInterface, DbConnectionInterface
{
    use DbConnection;

    /**
     * @var DbPool
     */
    protected $pool;

    /**
     * @var DbConnectionInterface
     */
    protected $connection;

    /**
     * @var ConnectionFactory
     */
    protected $factory;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var float
     */
    protected $lastUseTime = 0.0;

    protected $transaction = false;

    public function __construct(ContainerInterface $container, DbPool $pool, array $config)
    {
        parent::__construct($container, $pool);
        $this->factory = $container->get(ConnectionFactory::class);
        $this->config = $config;
        $this->context = $container->get(Context::class);

        $this->reconnect();
    }

    public function __call($name, $arguments)
    {
        return $this->connection->{$name}(...$arguments);
    }

    public function getConnection(): DbConnectionInterface
    {
        if ($this->check()) {
            return $this;
        }

        if (! $this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }

        return $this;
    }

    public function reconnect(): bool
    {
        $this->connection = $this->factory->make($this->config);
        $this->lastUseTime = microtime(true);
        return true;
    }

    public function check(): bool
    {
        $maxIdleTime = $this->config['max_idle_time'] ?? 60;
        $now = microtime(true);
        if ($now > $maxIdleTime + $this->lastUseTime) {
            return false;
        }

        $this->lastUseTime = $now;
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function release(): void
    {
        parent::release();
    }

    public function setTransaction(bool $transaction): void
    {
        $this->transaction = $transaction;
    }

    public function isTransaction(): bool
    {
        return $this->transaction;
    }
}
