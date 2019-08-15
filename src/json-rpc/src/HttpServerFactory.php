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

namespace Hyperf\JsonRpc;

use Hyperf\Rpc\ProtocolManager;
use Psr\Container\ContainerInterface;

/**
 * @deprecated v1.2
 */
class HttpServerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new HttpServer($container, $container->get(ProtocolManager::class));
    }
}
