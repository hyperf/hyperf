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
namespace Hyperf\ConfigEtcd;

use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class ClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return make(Client::class, [
            'client' => $container->get(KVInterface::class),
            'config' => $container->get(ConfigInterface::class),
        ]);
    }
}
