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

namespace Hyperf\Nats\Driver;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Nats\Exception\ConfigNotFoundException;
use Psr\Container\ContainerInterface;

class DriverFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var DriverInterface[]
     */
    protected $drivers = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class)->get('squeue', []);
    }

    public function get($pool = 'default'): DriverInterface
    {
        if (isset($this->drivers[$pool]) && $this->drivers[$pool] instanceof DriverInterface) {
            return $this->drivers[$pool];
        }

        $config = $this->config[$pool] ?? null;
        if (empty($config)) {
            throw new ConfigNotFoundException(sprintf('The config of %s is not found.', $pool));
        }

        return $this->drivers[$pool] = make($config['driver'], [
            'name' => $pool,
            'config' => $config,
        ]);
    }
}
