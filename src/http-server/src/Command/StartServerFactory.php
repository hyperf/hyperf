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

namespace Hyperf\HttpServer\Command;

use Psr\Container\ContainerInterface;

class StartServerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new StartServer($container);
    }
}
