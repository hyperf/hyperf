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

use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Aop\AroundInterface;
use Hyperf\Di\Aop\AstVisitorCollector;
use Hyperf\Di\Definition\ObjectDefinition;
use Hyperf\Di\Definition\PropertyHandlerManager;
use Hyperf\Di\Definition\PropertyInjection;
use Hyperf\Di\Definition\Reference;
use Hyperf\Di\Inject\InjectVisitor;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AspectCollector;
use Hyperf\Di\Aop\ProxyCallVisitor;
use Hyperf\Di\Command\InitProxyCommand;
use Hyperf\Di\Listener\BootApplicationListener;
use ReflectionClass;

class ConfigProvider
{
    public function __invoke(): array
    {
        // Register AST visitors to the collector.
        AstVisitorCollector::set(ProxyCallVisitor::class, ProxyCallVisitor::class);
        AstVisitorCollector::set(InjectVisitor::class, InjectVisitor::class);

        PropertyHandlerManager::register(Inject::class, function (ObjectDefinition $definition, string $propertyName, $annotationObject, $value, ReflectionClass $reflectionClass) {
            if (in_array(AroundInterface::class, $reflectionClass->getInterfaceNames())) {
                // Even the Inject has been handled by constructor of proxy class, but the Aspect class does not works,
                // So inject the value one more time here.
                /** @var Inject $injectAnnotation */
                if ($injectAnnotation = $value[Inject::class] ?? null) {
                    $propertyInjection = new PropertyInjection($propertyName, new Reference($injectAnnotation->value));
                    $definition->addPropertyInjection($propertyInjection);
                }
            }
        });

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
