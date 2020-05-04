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
use Hyperf\Di\Aop\AstVisitorCollector;
use Hyperf\Di\Aop\ProxyCallVisitor;
use Hyperf\Di\Aop\RegisterInjectPropertyHandler;
use Hyperf\Di\Command\InitProxyCommand;
use Hyperf\Di\Inject\InjectVisitor;
use Hyperf\Di\Listener\BootApplicationListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        // Register AST visitors to the collector.
        AstVisitorCollector::set(ProxyCallVisitor::class, ProxyCallVisitor::class);
        AstVisitorCollector::set(InjectVisitor::class, InjectVisitor::class);

        // Register Property Handler.
        RegisterInjectPropertyHandler::register();

        return [
            'dependencies' => [
                MethodDefinitionCollectorInterface::class => MethodDefinitionCollector::class,
                ClosureDefinitionCollectorInterface::class => ClosureDefinitionCollector::class,
            ],
            'listeners' => [
                BootApplicationListener::class,
            ],
            'autoload' => [
                'visitors' => [
                    ProxyCallVisitor::class,
                    InjectVisitor::class
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
