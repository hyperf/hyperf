<?php
declare(strict_types = 1);
namespace Hyperf\Nacos;

use Hyperf\Nacos\Config\FetchConfigProcess;
use Hyperf\Nacos\Config\OnPipeMessageListener;
use Hyperf\Nacos\Listener\BootAppConfListener;
use Hyperf\Nacos\Listener\OnShutdownListener;
use Hyperf\Nacos\Process\InstanceBeatProcess;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'commands' => [],
            'listeners' => [
                BootAppConfListener::class,
                OnShutdownListener::class,
                OnPipeMessageListener::class,
            ],
            'processes' => [
                InstanceBeatProcess::class,
                FetchConfigProcess::class,
            ],
            'dependencies' => [],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'nacos',
                    'description' => 'The config for nacos.',
                    'source' => __DIR__ . '/../publish/nacos.php',
                    'destination' => BASE_PATH . '/config/autoload/nacos.php',
                ],
            ],
        ];
    }
}
