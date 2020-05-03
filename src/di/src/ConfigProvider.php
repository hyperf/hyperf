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

use Hyperf\Di\Aop\AstVisitorCollector;
use Hyperf\Di\Inject\InjectVisitor;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AspectCollector;
use Hyperf\Di\Aop\ProxyCallVisitor;
use Hyperf\Di\Command\InitProxyCommand;
use Hyperf\Di\Listener\BootApplicationListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        // Register AST visitors to the collector.
        AstVisitorCollector::set(ProxyCallVisitor::class, ProxyCallVisitor::class);
        AstVisitorCollector::set(InjectVisitor::class, InjectVisitor::class);

        return [
            'dependencies' => [
                MethodDefinitionCollectorInterface::class => MethodDefinitionCollector::class,
            ],
            'listeners' => [
                BootApplicationListener::class,
            ],
            'autoload' => [
                'visitors' => [
                    ProxyCallVisitor::class
                ],
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
