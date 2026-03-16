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

namespace Hyperf\DbConnection;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Database\ConnectionInterface as DbConnectionInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\DbConnection\Pool\DbPool;
use Hyperf\DbConnection\Traits\DbConnection;
use Hyperf\Pool\Connection as BaseConnection;
use Hyperf\Pool\Exception\ConnectionException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class Connection extends BaseConnection implements ConnectionInterface, DbConnectionInterface
{
    use DbConnection;

    protected ?DbConnectionInterface $connection = null;

    protected ConnectionFactory $factory;

    protected LoggerInterface $logger;

    public function __construct(ContainerInterface $container, DbPool $pool, protected array $config)
    {
        parent::__construct($container, $pool);
        $this->factory = $container->get(ConnectionFactory::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);

        $this->reconnect();
    }

    public function __call($name, $arguments)
    {
        return $this->connection->{$name}(...$arguments);
    }

    public function getActiveConnection(): DbConnectionInterface
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
        $this->close();

        $this->connection = $this->factory->make($this->config);

        if ($this->connection instanceof \Hyperf\Database\Connection) {
            // Reset event dispatcher after db reconnect.
            if ($this->container->has(EventDispatcherInterface::class)) {
                $dispatcher = $this->container->get(EventDispatcherInterface::class);
                $this->connection->setEventDispatcher($dispatcher);
            }

            // Reset reconnector after db reconnect.
            $this->connection->setReconnector(function ($connection) {
                $this->logger->warning('Database connection refreshing.');
                if ($connection instanceof \Hyperf\Database\Connection) {
                    $this->refresh($connection);
                }
            });
        }

        $this->lastUseTime = microtime(true);
        return true;
    }

    public function close(): bool
    {
        if ($this->connection instanceof \Hyperf\Database\Connection) {
            $this->connection->disconnect();
        }

        unset($this->connection);

        return true;
    }

    public function isTransaction(): bool
    {
        return $this->transactionLevel() > 0;
    }

    public function release(): void
    {
        try {
            if ($this->connection instanceof \Hyperf\Database\Connection) {
                // Reset $recordsModified property of connection to false before the connection release into the pool.
                $this->connection->resetRecordsModified();
                if ($this->connection->getErrorCount() > 100) {
                    // If the error count of connection is more than 100, we think it is a bad connection,
                    // So we'll reset it at the next time
                    $this->lastUseTime = 0.0;
                }
            }

            if ($this->transactionLevel() > 0) {
                $this->rollBack(0);
                $this->logger->error('Maybe you\'ve forgotten to commit or rollback the MySQL transaction.');
            }
        } catch (Throwable $exception) {
            $this->logger->error('Rollback connection failed, caused by ' . $exception);
            // Ensure that the connection must be reset the next time after broken.
            $this->lastUseTime = 0.0;
        }

        parent::release();
    }

    /**
     * Refresh pdo and readPdo for current connection.
     */
    protected function refresh(\Hyperf\Database\Connection $connection)
    {
        $refresh = $this->factory->make($this->config);
        if ($refresh instanceof \Hyperf\Database\Connection) {
            $connection->disconnect();
            $connection->setPdo($refresh->getPdo());
            $connection->setReadPdo($refresh->getReadPdo());
        }

        $this->logger->warning('Database connection refreshed.');
    }
}
