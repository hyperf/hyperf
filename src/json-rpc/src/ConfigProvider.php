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
                DataFormatter::class => DataFormatterFactory::class,
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
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for json-rpc.',
                    'source' => __DIR__ . '/../publish/json_rpc.php',
                    'destination' => BASE_PATH . '/config/autoload/json_rpc.php',
                ],
            ],
        ];
    }
}
