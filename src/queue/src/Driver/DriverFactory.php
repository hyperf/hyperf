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

namespace Hyperf\Queue\Driver;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Queue\Exception\InvalidDriverException;
use Psr\Container\ContainerInterface;

class DriverFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var DriverInterface[]
     */
    protected $drivers = [];

    /**
     * @var array
     */
    protected $configs = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $config = $container->get(ConfigInterface::class);

        $this->configs = $config->get('queue', []);

        foreach ($this->configs as $key => $item) {
            $driverClass = $item['driver'];

            if (!class_exists($driverClass)) {
                throw new InvalidDriverException(sprintf('[Error] class %s is invalid.', $driverClass));
            }

            $driver = new $driverClass($this->container, $item);
            if (!$driver instanceof DriverInterface) {
                throw new InvalidDriverException(sprintf('[Error] class %s is not instanceof %s.', $driverClass, DriverInterface::class));
            }

            $this->drivers[$key] = $driver;
        }
    }

    public function __get($name)
    {
        $driver = $this->drivers[$name] ?? null;
        if (!$driver || !$driver instanceof DriverInterface) {
            throw new InvalidDriverException(sprintf('[Error]  %s is a invalid driver.', $name));
        }

        return $driver;
    }

    public function getConfig($name): array
    {
        return $this->configs[$name] ?? [];
    }
}
