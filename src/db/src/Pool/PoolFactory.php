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

namespace Hyperf\DB\Pool;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Container;
use Psr\Container\ContainerInterface;

class PoolFactory
{

    /**
     * @var AbstractPool[]
     */
    protected $pools = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getPool(string $name)
    {
        $config = $this->container->get(ConfigInterface::class);
        $key = sprintf('database.%s', $name);
        $dbConfig = $config->get($key);
        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }

        $class = $dbConfig['driver'] === 'swoole_mysql' ? SwooleMySqlPool::class : PDOPool::class;
        if ($this->container instanceof Container) {
            $pool = $this->container->make($class, ['name' => $name]);
        } else {
            $pool = new $class($this->container, $name);
        }
        $this->pools[$name] = $pool;
        return $this->pools[$name] = $pool;
    }
}
