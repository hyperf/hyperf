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

namespace Hyperf\Cache;

use Hyperf\Cache\Driver\DriverInterface;
use Hyperf\Cache\Driver\RedisDriver;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class CacheManager
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    protected $config;

    protected $drivers = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->config = $container->get(ConfigInterface::class)->get('cache', []);
    }

    public function getDriver($name)
    {
        if (isset($this->drivers[$name]) && $this->drivers[$name] instanceof DriverInterface) {
            return $this->drivers[$name];
        }

        if (! $this->config[$name]) {
            return false;
        }

        $driverClass = $this->config[$name]['driver'] ?? RedisDriver::class;
        $driver = new $driverClass($this->container, $this->config[$name]);

        return $this->drivers[$name] = $driver;
    }
}
