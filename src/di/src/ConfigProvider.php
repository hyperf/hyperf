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
use Hyperf\Di\Annotation\Bind;
use Hyperf\Di\Annotation\BindTo;
use Hyperf\Di\Annotation\InjectAspect;
use Hyperf\Di\Annotation\MultipleAnnotation;
use Hyperf\Di\Aop\AstVisitorRegistry;
use Hyperf\Di\Aop\PropertyHandlerVisitor;
use Hyperf\Di\Aop\ProxyCallVisitor;
use Hyperf\Di\Aop\RegisterInjectPropertyHandler;

class ConfigProvider
{
    public function __invoke(): array
    {
        // Register AST visitors to the collector.
        if (! AstVisitorRegistry::exists(PropertyHandlerVisitor::class)) {
            AstVisitorRegistry::insert(PropertyHandlerVisitor::class);
        }

        if (! AstVisitorRegistry::exists(ProxyCallVisitor::class)) {
            AstVisitorRegistry::insert(ProxyCallVisitor::class);
        }

        // Register Property Handler.
        RegisterInjectPropertyHandler::register();

        return [
            'dependencies' => [
                MethodDefinitionCollectorInterface::class => MethodDefinitionCollector::class,
                ClosureDefinitionCollectorInterface::class => ClosureDefinitionCollector::class,
                ...$this->collectAnnotationDependencies(),
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

    /**
     * Get the annotation dependencies.
     * @return array<class-string, class-string>
     */
    protected function collectAnnotationDependencies(): array
    {
        $dependencies = [];

        foreach (AnnotationCollector::getClassesByAnnotation(Bind::class) as $className => $metadata) {
            /**
             * @var MultipleAnnotation $metadata
             * @var Bind[] $annotations
             */
            $annotations = $metadata->toAnnotations();
            foreach ($annotations as $annotation) {
                $dependencies[$className] = $annotation->concrete;
            }
        }

        foreach (AnnotationCollector::getClassesByAnnotation(BindTo::class) as $className => $metadata) {
            /**
             * @var MultipleAnnotation $metadata
             * @var BindTo[] $annotations
             */
            $annotations = $metadata->toAnnotations();
            foreach ($annotations as $annotation) {
                $dependencies[$annotation->abstract] = $className;
            }
        }

        return $dependencies;
    }
}
