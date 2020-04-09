<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Pool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\FrequencyInterface;
use Hyperf\Contract\PoolInterface;
use Hyperf\Contract\PoolOptionInterface;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Throwable;

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

    /**
     * @var LowFrequencyInterface
     */
    protected $frequency;

    public function __construct(ContainerInterface $container, array $config = [])
    {
        $this->container = $container;
        $this->initOption($config);

        $this->channel = make(Channel::class, ['size' => $this->option->getMaxConnections()]);
    }

    public function get(): ConnectionInterface
    {
        $connection = $this->getConnection();

        if ($this->frequency instanceof FrequencyInterface) {
            $this->frequency->hit();
        }

        if ($this->frequency instanceof LowFrequencyInterface) {
            if ($this->frequency->isLowFrequency()) {
                $this->flush();
            }
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
            /** @var ConnectionInterface $conn */
            while ($this->currentConnections > $this->option->getMinConnections() && $conn = $this->channel->pop($this->option->getWaitTimeout())) {
                $conn->close();
                --$this->currentConnections;
                --$num;

                if ($num <= 0) {
                    // Ignore connections queued during flushing.
                    break;
                }
            }
        }
    }

    public function getCurrentConnections(): int
    {
        return $this->currentConnections;
    }

    public function getOption(): PoolOptionInterface
    {
        return $this->option;
    }

    public function getConnectionsInChannel(): int
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
            'maxIdleTime' => $options['max_idle_time'] ?? 60.0,
        ]);
    }

    abstract protected function createConnection(): ConnectionInterface;

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
            throw new RuntimeException('Connection pool exhausted. Cannot establish new connection before wait_timeout.');
        }
        return $connection;
    }
}
