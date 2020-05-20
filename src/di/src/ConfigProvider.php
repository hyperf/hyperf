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
use Hyperf\Di\Annotation\InjectAspect;
use Hyperf\Di\Aop\AstVisitorRegistry;
use Hyperf\Di\Aop\PropertyHandlerVisitor;
use Hyperf\Di\Aop\ProxyCallVisitor;
use Hyperf\Di\Aop\RegisterInjectPropertyHandler;
use Hyperf\Di\Listener\BootApplicationListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        // Register AST visitors to the collector.
        AstVisitorRegistry::insert(PropertyHandlerVisitor::class, PHP_INT_MAX / 2);
        AstVisitorRegistry::insert(ProxyCallVisitor::class, PHP_INT_MAX / 2);

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
            'aspects' => [
                InjectAspect::class,
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
