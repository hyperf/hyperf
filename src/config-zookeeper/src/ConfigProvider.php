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

namespace Hyperf\ConfigZookeeper;

use Hyperf\ConfigZookeeper\Listener\OnPipeMessageListener;
use Hyperf\ConfigZookeeper\Process\ConfigFetcherProcess;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ClientInterface::class => Client::class,
            ],
            'processes' => [
                ConfigFetcherProcess::class,
            ],
            'listeners' => [
                OnPipeMessageListener::class,
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
                    'description' => 'The config for zookeeper.',
                    'source' => __DIR__ . '/../publish/zookeeper.php',
                    'destination' => BASE_PATH . '/config/autoload/zookeeper.php',
                ],
            ],
        ];
    }
}
