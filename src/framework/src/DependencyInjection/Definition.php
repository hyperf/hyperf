<?php

namespace Hyperf\Framework\DependencyInjection;

use function \DI\factory as factory;
use function \DI\autowire as autowire;
use Hyperf\Bootstrap\WorkerStartCallback;
use Hyperf\Di\Definition\FactoryDefinition;
use Hyperf\Di\Definition\MethodInjection;
use Hyperf\Di\Definition\ObjectDefinition;
use Hyperf\Di\Definition\Reference;
use Hyperf\Di\ReflectionManager;
use function is_string;
use function is_array;
use function is_callable;

class Definition
{

    /**
     * Adapte more useful difinition syntax.
     */
    public static function reorganizeDefinitions(array $definitions): array
    {
        foreach ($definitions as $identifier => $definition) {
            if (is_string($definition) && class_exists($definition)) {
                if (method_exists($definition, '__invoke')) {
                    $definitions[$identifier] = factory($definition);
                } else {
                    $definitions[$identifier] = autowire($definition);
                }
            } elseif (is_array($definition) && is_callable($definition)) {
                $definitions[$identifier] = factory($definition);
            }
        }
        return $definitions;
    }

}