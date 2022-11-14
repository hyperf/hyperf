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
use Hyperf\Contract\PoolInterface;
use Hyperf\DB\Exception\DriverNotFoundException;
use Hyperf\DB\Exception\InvalidDriverException;
use Psr\Container\ContainerInterface;

class PoolFactory
{
    /**
     * @var Pool[]
     */
    protected array $pools = [];

    public function __construct(protected ContainerInterface $container)
    {
    }

    public function getPool(string $name): PoolInterface
    {
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }

        $config = $this->container->get(ConfigInterface::class);
        $driver = $config->get(sprintf('db.%s.driver', $name), 'pdo');
        $class = $this->getPoolName($driver);

        $pool = make($class, [$this->container, $name]);
        if (! $pool instanceof Pool) {
            throw new InvalidDriverException(sprintf('Driver %s is not invalid.', $driver));
        }
        return $this->pools[$name] = $pool;
    }

    protected function getPoolName(string $driver): string
    {
        switch (strtolower($driver)) {
            case 'mysql':
                return MySQLPool::class;
            case 'pdo':
                return PDOPool::class;
        }

        if (class_exists($driver)) {
            return $driver;
        }

        throw new DriverNotFoundException(sprintf('Driver %s is not found.', $driver));
    }
}
