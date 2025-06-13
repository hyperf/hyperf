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

namespace Hyperf\Nats\Driver;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Nats\Exception\ConfigNotFoundException;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class DriverFactory
{
    protected array $config;

    /**
     * @var DriverInterface[]
     */
    protected array $drivers = [];

    public function __construct(protected ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class)->get('nats', []);
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
