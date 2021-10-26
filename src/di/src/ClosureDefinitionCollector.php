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

class ClosureDefinitionCollector extends AbstractCallableDefinitionCollector implements ClosureDefinitionCollectorInterface
{
    public function getParameters(\Closure $closure): array
    {
        $key = spl_object_hash($closure);
        if (static::has($key)) {
            return static::get($key);
        }
        $reflectionFunction = new \ReflectionFunction($closure);
        $parameters = $reflectionFunction->getParameters();

        $definitions = $this->getDefinitionsFromParameters($parameters);
        static::set($key, $definitions);
        return $definitions;
    }

    public function getReturnType(\Closure $closure): ReflectionType
    {
        $key = spl_object_hash($closure) . '@return';
        if (static::has($key)) {
            return static::get($key);
        }
        $returnType = (new \ReflectionFunction($closure))->getReturnType();
        $type = $this->createType('', $returnType, $returnType ? $returnType->allowsNull() : true);
        static::set($key, $type);
        return $type;
    }
}
