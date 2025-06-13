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

namespace Hyperf\Server;

use Psr\Container\ContainerInterface;

class SwooleServerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $factory = $container->get(ServerFactory::class);

        return $factory->getServer()->getServer();
    }
}
