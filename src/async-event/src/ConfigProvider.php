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
namespace Hyperf\AsyncEvent;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
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
                    'id' => 'redis',
                    'description' => 'The config for redis.',
                    'source' => __DIR__ . '/../publish/redis.php',
                    'destination' => BASE_PATH . '/config/autoload/redis.php',
                ],
                [
                    'id' => 'async_queue',
                    'description' => 'The config for async.',
                    'source' => __DIR__ . '/../publish/async_queue.php',
                    'destination' => BASE_PATH . '/config/autoload/async_queue.php',
                ],
                [
                    'id' => 'dependencies',
                    'description' => 'The config for dependencies.',
                    'source' => __DIR__ . '/../publish/dependencies.php',
                    'destination' => BASE_PATH . '/config/autoload/dependencies.php',
                ],
                [
                    'id' => 'redis',
                    'description' => 'The config for redis.',
                    'source' => __DIR__ . '/../publish/redis.php',
                    'destination' => BASE_PATH . '/config/autoload/redis.php',
                ],
                [
                    'id' => 'processes',
                    'description' => 'The config for processes.',
                    'source' => __DIR__ . '/../publish/processes.php',
                    'destination' => BASE_PATH . '/config/autoload/processes.php',
                ],
            ],
        ];
    }
}
