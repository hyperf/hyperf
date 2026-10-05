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

    private int $leaseGeneration = 0;

    private bool $borrowed = false;

    public function __construct(ContainerInterface $container, DbPool $pool, protected array $config)
    {
        parent::__construct($container, $pool);
        $this->factory = $container->get(ConnectionFactory::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);

        $this->reconnect();
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->connection->{$name}(...$arguments);
    }

    /** @internal Called only when the pool lends this wrapper. */
    public function markBorrowed(): void
    {
        if ($this->borrowed) {
            throw new ConnectionException('A database connection cannot have two borrowers.');
        }
        $this->borrowed = true;
        ++$this->leaseGeneration;
    }

    public function getLeaseGeneration(): int
    {
        return $this->borrowed ? $this->leaseGeneration : 0;
    }

    public function getDatabaseConnection(): DbConnectionInterface
    {
        if ($this->connection === null) {
            throw new ConnectionException('Database connection is closed.');
        }
        return $this->connection;
    }

    public function invalidate(?Throwable $cause = null): void
    {
        if ($cause !== null) {
            $this->logger->error('Discarding database session state: ' . $cause);
        }
        try {
            $this->close();
        } catch (Throwable $exception) {
            $this->logger->error('Closing database connection failed: ' . $exception);
            $this->connection = null;
        } finally {
            $this->lastUseTime = 0.0;
        }
    }

    public function getActiveConnection(): DbConnectionInterface
    {
        if ($this->connection !== null && $this->check()) {
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
            // Driver initialization statements are not writes by the borrowing session.
            $this->connection->resetSessionState();
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

        $this->connection = null;

        return true;
    }

    public function isTransaction(): bool
    {
        return $this->transactionLevel() > 0;
    }

    public function release(): void
    {
        if (! $this->borrowed) {
            return;
        }
        $this->borrowed = false;
        try {
            if ($this->connection instanceof \Hyperf\Database\Connection) {
                // Request state and observers must not follow the connection into the pool.
                $this->connection->resetSessionState();
                if ($this->connection->getErrorCount() > 100) {
                    // If the error count of connection is more than 100, we think it is a bad connection,
                    // So we'll reset it at the next time
                    $this->lastUseTime = 0.0;
                }
            }

            if ($this->connection !== null && $this->transactionLevel() > 0) {
                $this->rollBack(0);
                $this->logger->error('Maybe you\'ve forgotten to commit or rollback the MySQL transaction.');
            }
        } catch (Throwable $exception) {
            $this->logger->error('Rollback connection failed, caused by ' . $exception);
            $this->invalidate();
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
