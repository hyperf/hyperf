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
namespace Hyperf\HttpServerRoute;

use Hyperf\HttpServerRoute\Listener\InitRouteCollectorListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                DataFormatter::class => DataFormatterFactory::class,
            ],
            'listeners' => [
                InitRouteCollectorListener::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
        ];
    }
}
