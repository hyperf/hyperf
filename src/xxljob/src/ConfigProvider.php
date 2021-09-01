<?php

declare(strict_types=1);

namespace Hyperf\XxlJob;

use Hyperf\XxlJob\Listener\BootAppRouteListener;
use Hyperf\XxlJob\Listener\MainWorkerStartListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Application::class => ApplicationFactory::class,
            ],
            'listeners' => [
                BootAppRouteListener::class,
                MainWorkerStartListener::class,
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
                    'description' => 'The config for xxl_job.',
                    'source' => __DIR__ . '/../publish/xxl_job.php',
                    'destination' => BASE_PATH . '/config/autoload/xxl_job.php',
                ],
            ],
        ];
    }
}
