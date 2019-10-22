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

namespace Hyperf\Etcd;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Etcd\Exception\ClientNotFindException;
use Psr\Container\ContainerInterface;

class KVFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $version = $config->get('etcd.version');

        $params = ['config' => $config];

        switch ($version) {
            case 'v3':
            case 'v3alpha':
            case 'v3beta':
                return make(V3\KV::class, $params);
        }

        throw new ClientNotFindException(sprintf("KV of {$version} is not find."));
    }
}
