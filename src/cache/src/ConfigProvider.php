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
namespace Hyperf\Cache;

use Hyperf\Cache\Listener\DeleteListener;
use Psr\SimpleCache\CacheInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                CacheInterface::class => Cache::class,
            ],
            'listeners' => [
                DeleteListener::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                    'collectors' => [
                        CacheListenerCollector::class,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for cache.',
                    'source' => __DIR__ . '/../publish/cache.php',
                    'destination' => BASE_PATH . '/config/autoload/cache.php',
                ],
            ],
        ];
    }
}
