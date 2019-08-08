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
use Hyperf\ServiceGovernance\ServiceManager;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
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
