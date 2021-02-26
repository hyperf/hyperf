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
namespace Hyperf\ModelListener;

use Hyperf\ModelListener\Collector\ListenerCollector;
use Hyperf\ModelListener\Listener\ModelEventListener;
use Hyperf\ModelListener\Listener\ModelHookEventListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                ModelEventListener::class,
                ModelHookEventListener::class => 99,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                    'collectors' => [
                        ListenerCollector::class,
                    ],
                ],
            ],
        ];
    }
}
