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

interface MethodDefinitionCollectorInterface
{
    /**
     * Retrieve the metadata for the parameters of the method.
     * @return ReflectionType[]
     */
    public function getParameters(string $class, string $method): array;

    /**
     * Retrieve the metadata for the return value of the method.
     */
    public function getReturnType(string $class, string $method): ReflectionType;
}
