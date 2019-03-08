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

namespace Hyperf\Pool;

use Throwable;
use RuntimeException;
use Hyperf\Utils\Coroutine;
use Hyperf\Contract\PoolInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\PoolOptionInterface;

abstract class Pool implements PoolInterface
{
    /**
     * @var Channel
     */
    protected $channel;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var PoolOptionInterface
     */
    protected $option;

    /**
     * @var int
     */
    protected $currentConnections = 0;

    public function __construct(ContainerInterface $container, array $config = [])
    {
        $this->container = $container;
        $this->initOption($config);

        $this->channel = make(Channel::class, ['size' => $this->option->getMaxConnections()]);
    }

    public function get(): ConnectionInterface
    {
        $connection = $this->getConnection();

        if (Coroutine::inCoroutine()) {
            // Release the connecion before the current coroutine end.
            defer(function () use ($connection) {
                $connection->release();
            });
        }

        return $connection;
    }

    public function release(ConnectionInterface $connection): void
    {
        $this->channel->push($connection);
    }

    public function flush(): void
    {
        $num = $this->getConnectionsInChannel();

        if ($num > 0) {
            while ($conn = $this->channel->pop($this->option->getWaitTimeout())) {
                $conn->close();
            }
        }
    }

    public function getCurrentConnections(): int
    {
        return $this->currentConnections;
    }

    protected function getConnectionsInChannel(): int
    {
        return $this->channel->length();
    }

    protected function initOption(array $options = []): void
    {
        $this->option = make(PoolOption::class, [
            'minConnections' => $options['min_connections'] ?? 1,
            'maxConnections' => $options['max_connections'] ?? 10,
            'connectTimeout' => $options['connect_timeout'] ?? 10.0,
            'waitTimeout' => $options['wait_timeout'] ?? 3.0,
            'heartbeat' => $options['heartbeat'] ?? -1,
        ]);
    }

    abstract protected function createConnection(): ConnectionInterface;

    /**
     * Get id of connection for context.
     * @return string
     */
    abstract protected function getConnectionId(): string;

    private function getConnection(): ConnectionInterface
    {
        $num = $this->getConnectionsInChannel();

        try {
            if ($num === 0 && $this->currentConnections < $this->option->getMaxConnections()) {
                ++$this->currentConnections;
                return $this->createConnection();
            }
        } catch (Throwable $throwable) {
            --$this->currentConnections;
            throw $throwable;
        }

        $connection = $this->channel->pop($this->option->getWaitTimeout());
        if (! $connection instanceof ConnectionInterface) {
            throw new RuntimeException('Cannot pop the connection, pop timeout.');
        }
        return $connection;
    }
}
