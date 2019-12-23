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

namespace Hyperf\Scout;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;

class EngineFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $name = $config->get('scout.default');
        $driver = $config->get("scout.engine.{$name}.driver");
        $driverInstance = make($driver);
        return $driverInstance->make($name);
    }
}
