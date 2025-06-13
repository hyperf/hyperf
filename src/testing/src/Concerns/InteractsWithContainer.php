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

namespace Hyperf\Testing\Concerns;

use Closure;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ApplicationInterface;
use Hyperf\Di\Container;
use Hyperf\Di\Definition\DefinitionSourceFactory;
use Mockery;
use Mockery\MockInterface;
use Psr\Container\ContainerInterface;

trait InteractsWithContainer
{
    /**
     * @var null|Container|ContainerInterface
     */
    protected ?ContainerInterface $container = null;

    /**
     * Register an instance of an object in the container.
     *
     * @param string $abstract
     * @param object $instance
     * @return object
     */
    protected function swap($abstract, $instance)
    {
        return $this->instance($abstract, $instance);
    }

    /**
     * Register an instance of an object in the container.
     *
     * @param string $abstract
     * @param object $instance
     * @return object
     */
    protected function instance($abstract, $instance)
    {
        /* @phpstan-ignore-next-line */
        $this->container->set($abstract, $instance);

        return $instance;
    }

    /**
     * Mock an instance of an object in the container.
     *
     * @param string $abstract
     * @return MockInterface
     */
    protected function mock($abstract, ?Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args())));
    }

    /**
     * Mock a partial instance of an object in the container.
     *
     * @param string $abstract
     * @return MockInterface
     */
    protected function partialMock($abstract, ?Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::mock(...array_filter(func_get_args()))->makePartial());
    }

    /**
     * Spy an instance of an object in the container.
     *
     * @param string $abstract
     * @return MockInterface
     */
    protected function spy($abstract, ?Closure $mock = null)
    {
        return $this->instance($abstract, Mockery::spy(...array_filter(func_get_args())));
    }

    protected function createContainer(): ContainerInterface
    {
        return new Container((new DefinitionSourceFactory())());
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    protected function flushContainer(): void
    {
        $this->container = null;
    }

    protected function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    protected function refreshContainer(): void
    {
        $this->container = ApplicationContext::setContainer($this->createContainer());
        $this->container->get(ApplicationInterface::class);
    }
}
