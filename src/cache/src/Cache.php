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

use Hyperf\Cache\Exception\CacheException;
use Hyperf\Utils\ApplicationContext;
use Psr\SimpleCache\CacheInterface;

class Cache
{
    protected $driver;

    public function __construct(CacheManager $manager)
    {
        $this->driver = $manager->getDriver();
    }

    public function __call($name, $arguments)
    {
        return $this->driver->{$name}(...$arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        $container = ApplicationContext::getContainer();
        if (! $container->has(CacheInterface::class)) {
            throw new CacheException(sprintf("No entry or class found for '%s'", CacheInterface::class));
        }

        $cache = $container->get(CacheInterface::class);

        return $cache->{$name}(...$arguments);
    }

    /**
     * @return Driver\DriverInterface
     */
    public function getDriver(): Driver\DriverInterface
    {
        return $this->driver;
    }
}
