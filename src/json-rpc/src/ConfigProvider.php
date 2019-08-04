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

use Hyperf\JsonRpc\Listener\RegisterProtocolListener;
use Hyperf\JsonRpc\Listener\RegisterServiceListener;
use Hyperf\Rpc\Contract\PackerInterface;
use Hyperf\Rpc\Contract\PathGeneratorInterface;
use Hyperf\Rpc\PathGenerator;
use Hyperf\ServiceGovernance\ServiceManager;
use Hyperf\Utils\Packer\JsonPacker;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                PackerInterface::class => JsonPacker::class,
                PathGeneratorInterface::class => PathGenerator::class,
                TcpServer::class => TcpServerFactory::class,
                HttpServer::class => HttpServerFactory::class,
                DataFormatter::class => DataFormatterFactory::class,
            ],
            'commands' => [
            ],
            'listeners' => [
                RegisterProtocolListener::class,
                value(function () {
                    if (class_exists(ServiceManager::class)) {
                        return RegisterServiceListener::class;
                    }
                    return null;
                }),
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
        ];
    }
}
