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

namespace Hyperf\RpcServer;

use Hyperf\Rpc\Contract\PackerInterface;
use Hyperf\RpcServer\Register\Adapter\ConsulAgent;
use Hyperf\RpcServer\Register\Adapter\ConsulAgentFactory;
use Hyperf\Utils\Packer\JsonPacker;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Server::class => ServerFactory::class,
                PackerInterface::class => JsonPacker::class,
                ConsulAgent::class => ConsulAgentFactory::class,
            ],
            'commands' => [
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
        ];
    }
}
