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

namespace Hyperf\Contract;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Build an entry of the container by its name.
     * This method behave like get() except resolves the entry again every time.
     * For example if the entry is a class then a new instance will be created each time.
     * This method makes the container behave like a factory.
     *
     * @template TClass
     *
     * @param class-string<TClass>|string $name entry name or a class name
     * @param array $parameters Optional parameters to use to build the entry. Use this to force specific parameters to specific values. Parameters not defined in this array will be resolved using the container.
     *
     * @return ($name is class-string<TClass> ? TClass : mixed)
     *
     * @throws ContainerExceptionInterface&Throwable the name parameter must be of type string
     * @throws NotFoundExceptionInterface&Throwable no entry found for the given name
     */
    public function make(string $name, array $parameters = []);

    /**
     * Bind an arbitrary resolved entry to an identifier.
     * Useful for testing 'get'.
     *
     * @param mixed $entry
     */
    public function set(string $name, $entry): void;

    /**
     * Unbind an arbitrary resolved entry.
     */
    public function unbind(string $name): void;

    /**
     * Bind an arbitrary definition to an identifier.
     * Useful for testing 'make'.
     *
     * @param array|callable|string $definition
     */
    public function define(string $name, $definition): void;
}
