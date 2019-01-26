<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Event;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ListenerProviderInterface::class => ListenerProviderFactory::class,
                EventDispatcherInterface::class => EventDispatcher::class,
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
        ];
    }
}
