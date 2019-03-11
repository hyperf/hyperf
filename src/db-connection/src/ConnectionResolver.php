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

use Psr\Container\ContainerInterface;
use Hyperf\Database\ConnectionInterface;
use Hyperf\DbConnection\Pool\PoolFactory;
use Hyperf\Database\ConnectionResolverInterface;

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
     * @param string $name
     * @return ConnectionInterface
     */
    public function connection($name = null)
    {
        if (is_null($name)) {
            $name = $this->getDefaultConnection();
        }

        $pool = $this->factory->getPool($name);

        return $pool->get()->getConnection();
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
