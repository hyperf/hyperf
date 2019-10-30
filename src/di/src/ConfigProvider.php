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

namespace Hyperf\Di;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AspectCollector;
use Hyperf\Di\Command\InitProxyCommand;
use Hyperf\Di\Listener\BootApplicationListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                MethodDefinitionCollectorInterface::class => MethodDefinitionCollector::class,
            ],
            'commands' => [
                InitProxyCommand::class,
            ],
            'listeners' => [
                BootApplicationListener::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                    'collectors' => [
                        AnnotationCollector::class,
                        AspectCollector::class,
                    ],
                ],
            ],
        ];
    }
}
