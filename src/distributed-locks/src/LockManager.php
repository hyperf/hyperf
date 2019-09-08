<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DistributedLocks;

use Hyperf\DistributedLocks\Driver\DriverInterface;
use Hyperf\DistributedLocks\Driver\RedisDriver;
use Hyperf\DistributedLocks\Exception\InvalidArgumentException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use function call;

class LockManager
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    protected $drivers = [];

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ConfigInterface $config, StdoutLoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function getDriver($name = 'redis'): DriverInterface
    {
        if (isset($this->drivers[$name]) && $this->drivers[$name] instanceof DriverInterface) {
            return $this->drivers[$name];
        }

        $config = $this->config->get("distributed-locks.{$name}");
        if (empty($config)) {
            throw new InvalidArgumentException(sprintf('The lock config %s is invalid.', $name));
        }

        $driverClass = $config['driver'] ?? RedisDriver::class;

        $driver = make($driverClass, ['config' => $config]);

        return $this->drivers[$name] = $driver;
    }

    public function call($callback, string $key, int $ttl = 3600, $config = 'redis')
    {
        $driver = $this->getDriver($config);

        $locker = $driver->lock($key,$ttl);
        if (!$locker) {
            // todo
        }
        try {
            $result = call($callback);
        } catch (\Throwable $throwable) {
            throw $throwable;
        } finally {
            $driver->unlock([]);
        }

        return $result;
    }
}
