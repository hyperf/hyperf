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

namespace Hyperf\GrpcServer;

use Hyperf\GrpcServer\Listener\RegisterProtocolListener;
use Hyperf\GrpcServer\Listener\RegisterServiceListener;
use Hyperf\ServiceGovernance\ServiceManager;

use function Hyperf\Support\value;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                RegisterProtocolListener::class,
                value(function () {
                    if (class_exists(ServiceManager::class)) {
                        return RegisterServiceListener::class;
                    }
                    return null;
                }),
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The configuration file for grpc rpc server',
                    'source' => __DIR__ . '/../publish/grpc_server.php',
                    'destination' => BASE_PATH . '/config/autoload/grpc_server.php',
                ],
            ],
        ];
    }
}
