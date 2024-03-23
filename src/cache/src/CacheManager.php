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

namespace Hyperf\Cache;

use Hyperf\Cache\Driver\DriverInterface;
use Hyperf\Cache\Driver\RedisDriver;
use Hyperf\Cache\Exception\InvalidArgumentException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;

use function Hyperf\Support\make;

class CacheManager
{
    /**
     * @var DriverInterface[]
     */
    protected array $drivers = [];

    public function __construct(protected ConfigInterface $config, protected StdoutLoggerInterface $logger)
    {
    }

    public function getDriver(string $name = 'default'): DriverInterface
    {
        if (isset($this->drivers[$name]) && $this->drivers[$name] instanceof DriverInterface) {
            return $this->drivers[$name];
        }

        $config = $this->config->get("cache.{$name}");
        if (empty($config)) {
            throw new InvalidArgumentException(sprintf('The cache config %s is invalid.', $name));
        }

        $driverClass = $config['driver'] ?? RedisDriver::class;

        $driver = make($driverClass, ['config' => $config]);

        return $this->drivers[$name] = $driver;
    }

    public function call(callable $callback, string $key, int $ttl = 3600, $config = 'default')
    {
        $driver = $this->getDriver($config);

        [$has, $result] = $driver->fetch($key);
        if ($has) {
            return $result;
        }

        $result = $callback();
        $driver->set($key, $result, $ttl);

        return $result;
    }
}
