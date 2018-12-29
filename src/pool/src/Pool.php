<?php
/**
 * Created by PhpStorm.
 * User: limx
 * Date: 2018/12/29
 * Time: 3:01 PM
 */

namespace Hyperf\Pool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Contract\PoolInterface;
use Hyperf\Contract\PoolOptionInterface;
use Psr\Container\ContainerInterface;
use Swoole\Coroutine\Channel;

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

    protected $currentConnections = 0;

    /**
     * @param Channel $channel
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->option = $container->get($this->optionName);
        $this->channel = new Channel($this->option->getMaxConnections());
    }

    public function get(): ConnectionInterface
    {
        $num = $this->getConnectionsInChannel();

        if ($num == 0 && $this->currentConnections < $this->option->getMaxConnections()) {
            $connection = $this->createConnection();
            $this->currentConnections++;
            return $connection;
        }

        return $this->channel->pop($this->option->getWaitTimeout());
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

    protected function getConnectionsInChannel(): int
    {
        $stats = $this->channel->stats();
        return $stats['queue_num'];
    }

    abstract protected function createConnection(): ConnectionInterface;
}