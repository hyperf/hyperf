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

namespace Hyperf\GrpcServer;

use Psr\Container\ContainerInterface;

/**
 * @deprecated v1.1
 */
class ServerFactory
{
    public function __invoke(ContainerInterface $container): Server
    {
        return new Server($container);
    }
}
