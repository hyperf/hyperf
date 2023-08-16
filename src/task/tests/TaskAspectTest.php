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
namespace HyperfTest\Task;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Task\Aspect\TaskAspect;
use Hyperf\Task\Task;
use Hyperf\Task\TaskExecutor;
use HyperfTest\Task\Stub\Foo;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class TaskAspectTest extends TestCase
{
    protected $isTaskEnvironment = false;

    protected function tearDown(): void
    {
        Mockery::close();
        $this->isTaskEnvironment = false;
    }

    public function testTaskAspect()
    {
        $container = $this->getContainer();

        $aspect = new TaskAspect($container);
        $closure = function ($id, $name) {
            return ['id' => $id, 'name' => $name];
        };
        $point = new ProceedingJoinPoint($closure, Foo::class, 'getIdAndName', [
            'keys' => $data = ['id' => uniqid(), 'name' => 'Hyperf'],
            'order' => ['id', 'name'],
        ]);
        $point->pipe = function (ProceedingJoinPoint $point) {
            return $point->processOriginalMethod();
        };

        $res = $aspect->process($point);
        $this->assertSame($data, $res);

        $this->isTaskEnvironment = true;
        $res = $aspect->process($point);
        $this->assertSame($data, $res);
    }

    public function testTaskAspectVariadic()
    {
        $container = $this->getContainer();

        $aspect = new TaskAspect($container);
        $closure = function ($id, $arguments) {
            return ['id' => $id, 'arguments' => $arguments];
        };
        $point = new ProceedingJoinPoint($closure, Foo::class, 'dump', [
            'keys' => $data = ['id' => 1, 'arguments' => [1, 2, 3]],
            'order' => ['id', 'arguments'],
        ]);
        $point->pipe = function (ProceedingJoinPoint $point) {
            return $point->processOriginalMethod();
        };

        $res = $aspect->process($point);
        $this->assertSame($data, $res);

        $this->isTaskEnvironment = true;
        $res = $aspect->process($point);
        $this->assertSame($data, $res);
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(Foo::class)->andReturn(new Foo());

        $container->shouldReceive('get')->with(TaskExecutor::class)->andReturnUsing(function () use ($container) {
            $executor = Mockery::mock(TaskExecutor::class);
            $executor->shouldReceive('isTaskEnvironment')->andReturn($this->isTaskEnvironment);
            $executor->shouldReceive('execute')->with(Mockery::any(), Mockery::any())->andReturnUsing(function (Task $task, $_) use ($container) {
                [$class, $method] = $task->callback;
                return $container->get($class)->{$method}(...$task->arguments);
            });
            return $executor;
        });

        return $container;
    }
}
