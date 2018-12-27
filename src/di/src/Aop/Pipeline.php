<?php

namespace Hyperflex\Di\Aop;


use Closure;
use Hyperflex\Di\Exception\InvalidDefinitionException;

class Pipeline extends \Hyperflex\Utils\Pipeline
{
    protected function carry(): Closure
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                if (is_string($pipe) && class_exists($pipe)) {
                    $pipe = $this->container->get($pipe);
                }
                if (! $passable instanceof ProceedingJoinPoint) {
                    throw new InvalidDefinitionException('$passable must is a ProceedingJoinPoint object.');
                }
                $passable->pipe = $stack;
                return method_exists($pipe, $this->method) ? $pipe->{$this->method}($passable) : $pipe($passable);
            };
        };
    }


}