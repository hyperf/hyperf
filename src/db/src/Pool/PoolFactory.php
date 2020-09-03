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
namespace Hyperf\DB\Pool;

use Hyperf\Contract\ConfigInterface;
use Hyperf\DB\Exception\DriverNotFoundException;
use Psr\Container\ContainerInterface;

class PoolFactory
{
    /**
     * @var Pool[]
     */
    protected $pools = [];

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getPool(string $name)
    {
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }

        $config = $this->container->get(ConfigInterface::class);
        $driver = $config->get(sprintf('db.%s.driver', $name), 'pdo');
        $class = $this->getPoolName($driver);

        return $this->pools[$name] = make($class, [$this->container, $name]);
    }

    protected function getPoolName(string $driver)
    {
        switch (strtolower($driver)) {
            case 'mysql':
                return MySQLPool::class;
            case 'pdo':
                return PDOPool::class;
        }

        throw new DriverNotFoundException(sprintf('Driver %s is not found.', $driver));
    }
}
