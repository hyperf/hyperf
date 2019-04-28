<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\RpcClient;

use Hyperf\Rpc\Contract\PackerInterface;
use Hyperf\RpcClient\Pool\PoolFactory;
use Psr\Container\ContainerInterface;

class ClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $packer = $container->get(PackerInterface::class);
        $config = [
            'host' => '0.0.0.0',
            'port' => 9502,
            'options' => [
                'open_eof_check' => true,
                'package_eof' => "\r\n",
            ],
        ];
        $poolFactory = $container->get(PoolFactory::class);
        return new Client($config, $packer, $poolFactory);
    }
}
