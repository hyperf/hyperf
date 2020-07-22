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

use Hyperf\ConfigEtcd\Listener\BootProcessListener;
use Hyperf\ConfigEtcd\Listener\OnPipeMessageListener;
use Hyperf\ConfigEtcd\Process\ConfigFetcherProcess;

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
                BootProcessListener::class,
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
                    'description' => 'The config for config_etcd.',
                    'source' => __DIR__ . '/../publish/config_etcd.php',
                    'destination' => BASE_PATH . '/config/autoload/config_etcd.php',
                ],
            ],
        ];
    }
}
