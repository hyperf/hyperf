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
use Hyperf\Pool\Connection as BaseConnection;
use Hyperf\Pool\Exception\ConnectionException;
use Psr\Container\ContainerInterface;

class Connection extends BaseConnection implements ConnectionInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

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

    public function __construct(ContainerInterface $container, array $config)
    {
        $this->container = $container;
        $this->factory = $container->get(ConnectionFactory::class);
        $this->config = $config;

        $this->reconnect();
    }

    public function getConnection(): DbConnectionInterface
    {
        if ($this->check()) {
            return $this->connection;
        }

        if (!$this->reconnect()) {
            throw new ConnectionException('Connection reconnect failed.');
        }

        return $this->connection;
    }

    public function reconnect(): bool
    {
        $this->connection = $this->factory->make($this->config);
        return true;
    }

    public function check(): bool
    {
        return true;
    }

    public function close(): bool
    {
        return true;
    }
}
