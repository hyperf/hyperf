<?php

namespace Hyperf\Di\Aop;


use Hyperf\Di\Annotation\Inject;
use Hyperf\Di\Definition\ObjectDefinition;
use Hyperf\Di\Definition\PropertyHandlerManager;
use Hyperf\Di\Definition\PropertyInjection;
use Hyperf\Di\Definition\Reference;
use ReflectionClass;

class RegisterInjectPropertyHandler
{

    /**
     * Even the Inject has been handled by constructor of proxy class, but the Aspect class does not works,
     * So inject the value one more time here.
     */
    public static function register()
    {
        PropertyHandlerManager::register(Inject::class, function (ObjectDefinition $definition, string $propertyName, $annotationObject, $value, ReflectionClass $reflectionClass) {
            if (in_array(AroundInterface::class, $reflectionClass->getInterfaceNames())) {
                /** @var Inject $injectAnnotation */
                if ($injectAnnotation = $value[Inject::class] ?? null) {
                    $propertyInjection = new PropertyInjection($propertyName, new Reference($injectAnnotation->value));
                    $definition->addPropertyInjection($propertyInjection);
                }
            }
        });
    }

}