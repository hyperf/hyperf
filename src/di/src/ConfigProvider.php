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

namespace Hyperf\Di;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AspectCollector;
use Hyperf\Di\Annotation\InjectAspect;
use Hyperf\Di\Aop\AstVisitorRegistry;
use Hyperf\Di\Aop\PropertyHandlerVisitor;
use Hyperf\Di\Aop\ProxyCallVisitor;
use Hyperf\Di\Aop\RegisterInjectPropertyHandler;

class ConfigProvider
{
    public function __invoke(): array
    {
        // 经测试 enterNode 和 leaveNode 符合洋葱模型，权重高的 enterNode 先执行，但是 leaveNode 后执行
        // According to tests, both enterNode and leaveNode conform to the onion model: enterNode with higher priority executes first, while leaveNode executes later.
        if (! AstVisitorRegistry::exists(ProxyCallVisitor::class)) {
            AstVisitorRegistry::insert(ProxyCallVisitor::class);
        }

        // Register AST visitors to the collector.
        if (! AstVisitorRegistry::exists(PropertyHandlerVisitor::class)) {
            AstVisitorRegistry::insert(PropertyHandlerVisitor::class);
        }

        // Register Property Handler.
        RegisterInjectPropertyHandler::register();

        return [
            'dependencies' => [
                MethodDefinitionCollectorInterface::class => MethodDefinitionCollector::class,
                ClosureDefinitionCollectorInterface::class => ClosureDefinitionCollector::class,
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
                'ignore_annotations' => [
                    'mixin',
                ],
            ],
        ];
    }
}
