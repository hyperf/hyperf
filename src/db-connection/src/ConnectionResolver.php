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

use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\DbConnection\Pool\PoolFactory;
use Psr\Container\ContainerInterface;

class ConnectionResolver implements ConnectionResolverInterface
{
    /**
     * The default connection name.
     *
     * @var string
     */
    protected $default = 'default';

    /**
     * @var PoolFactory
     */
    protected $factory;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->factory = $container->get(PoolFactory::class);
    }

    /**
     * Get a database connection instance.
     *
     * @param  string              $name
     * @return ConnectionInterface
     */
    public function connection($name = null)
    {
        if (is_null($name)) {
            $name = $this->getDefaultConnection();
        }

        $context = $this->container->get(Context::class);
        $connection = $context->connection($name);
        if ($connection) {
            return $connection->getConnection();
        }

        $pool = $this->factory->getDbPool($name);

        $connection = $pool->get();

        $context->set($name, $connection);

        return $connection->getConnection();
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->default;
    }

    /**
     * Set the default connection name.
     *
     * @param string $name
     */
    public function setDefaultConnection($name)
    {
        $this->default = $name;
    }
}
