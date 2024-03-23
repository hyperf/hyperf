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

namespace Hyperf\ConfigCenter;

use Hyperf\ConfigCenter\Contract\DriverInterface;
use Hyperf\Contract\ConfigInterface;

use function Hyperf\Support\make;

class DriverFactory
{
    public function __construct(protected ConfigInterface $config)
    {
    }

    public function create(string $driver, array $properties = []): DriverInterface
    {
        $defaultDriver = $this->config->get('config_center.driver', '');
        $config = $this->config->get('config_center.drivers.' . $driver, []);
        $class = $config['driver'] ?? $defaultDriver;
        $instance = make($class, $config);
        foreach ($properties as $method => $value) {
            if (method_exists($instance, $method)) {
                $instance->{$method}($value);
            }
        }
        return $instance;
    }
}
