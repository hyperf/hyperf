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

namespace Hyperf\DistributedLock;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\DistributedLock\Driver\ConsulDriver;
use Hyperf\DistributedLock\Driver\DriverInterface;
use Hyperf\DistributedLock\Driver\RedisDriver;
use Hyperf\DistributedLock\Exception\InvalidArgumentException;
use Psr\Container\ContainerInterface;

class LockManager
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var array
     */
    protected $drivers = [];

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $registerDriverClasses = [
        'redis' => RedisDriver::class,
        'consul' => ConsulDriver::class,
    ];

    public function __construct(ContainerInterface $container, ConfigInterface $config, StdoutLoggerInterface $logger)
    {
        $this->container = $container;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param string $name
     * @return DriverInterface
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function getDriver(string $name = 'redis'): DriverInterface
    {
        if (isset($this->drivers[$name]) && $this->drivers[$name] instanceof DriverInterface) {
            return $this->drivers[$name];
        }

        $config = $this->config->get("distributed-lock.{$name}");
        if (empty($config)) {
            throw new InvalidArgumentException(sprintf('The lock driver config %s is invalid.', $name));
        }

        $driverClass = $this->registerDriverClasses[$name] ?? '';
        if (! $driverClass) {
            throw new InvalidArgumentException(sprintf('The lock driver %s is not registered.', $name));
        }

        $driver = make($driverClass, ['config' => $config, 'container' => $this->container]);

        return $this->drivers[$name] = $driver;
    }

    /**
     * @param string $name
     * @param string $driverClass
     *
     * Author: wangyi <chunhei2008@qq.com>
     */
    public function registerDriver(string $name, string $driverClass)
    {
        if (! class_exists($driverClass)) {
            throw new InvalidArgumentException(sprintf('The lock driver class %s is not exists.', $name));
        }
        $this->registerDriverClasses[$name] = $driverClass;
    }
}
