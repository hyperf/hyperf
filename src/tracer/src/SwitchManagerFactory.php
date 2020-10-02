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
namespace Hyperf\Tracer;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class SwitchManagerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $manager = new SwitchManager();
        $manager->apply($config->get('opentracing.enable', []));
        return $manager;
    }
}
