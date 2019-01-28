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

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\PoolInterface;
use Hyperf\Contract\PoolOptionInterface;
use Psr\Container\ContainerInterface;

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
     * @var string
     */
    protected $optionName = PoolOption::class;

    /**
     * @var PoolOptionInterface
     */
    protected $option;

    /**
     * @var int
     */
    protected $currentConnections = 0;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->initOption();

        $this->channel = new Channel($this->option->getMaxConnections());
    }

    public function get(): ConnectionInterface
    {
        $num = $this->getConnectionsInChannel();

        if ($num === 0 && $this->currentConnections < $this->option->getMaxConnections()) {
            $connection = $this->createConnection();
            ++$this->currentConnections;
            return $connection;
        }

        $result = $this->channel->pop($this->option->getWaitTimeout());
        if (! $result instanceof ConnectionInterface) {
            throw new \RuntimeException('Cannot pop the connection.');
        }
        return $result;
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

    protected function initOption()
    {
        $this->option = $this->container->get($this->optionName);
    }

    abstract protected function createConnection(): ConnectionInterface;
}
