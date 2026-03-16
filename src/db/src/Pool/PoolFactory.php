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
use Hyperf\DB\PgSQL\PgSQLPool;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

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
        $driver = $config->get(sprintf('db.%s.driver', $name), 'mysql');
        $class = $this->getPoolName($driver);
        $pool = make($class, [$this->container, $name]);

        if (! $pool instanceof Pool) {
            throw new InvalidDriverException(sprintf('Driver %s is not invalid.', $driver));
        }

        return $this->pools[$name] = $pool;
    }

    protected function getPoolName(string $driver): string
    {
        return match (strtolower($driver)) {
            'mysql', 'pdo' => MySQLPool::class,
            'pgsql' => PgSQLPool::class,
            default => class_exists($driver) ? $driver : throw new DriverNotFoundException(sprintf('Driver %s is not found.', $driver)),
        };
    }
}
