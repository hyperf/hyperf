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
namespace HyperfTest\ExceptionHandler;

use Exception;
use Hyperf\Context\Context;
use Hyperf\ExceptionHandler\ExceptionHandlerDispatcher;
use Hyperf\HttpMessage\Base\Response;
use HyperfTest\ExceptionHandler\Stub\BarExceptionHandler;
use HyperfTest\ExceptionHandler\Stub\FooExceptionHandler;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

use function Hyperf\Coroutine\parallel;

/**
 * @internal
 * @coversNothing
 */
class ExceptionHandlerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        Context::set('test.exception-handler.latest-handler', null);
    }

    public function testStopPropagation()
    {
        $handlers = [
            BarExceptionHandler::class,
            FooExceptionHandler::class,
        ];

        $container = $this->getContainer();

        parallel([function () use ($container, $handlers) {
            $exception = new Exception('xxx', 500);

            Context::set(ResponseInterface::class, new Response());

            $dispatcher = new ExceptionHandlerDispatcher($container);
            $dispatcher->dispatch($exception, $handlers);

            $this->assertSame(FooExceptionHandler::class, Context::get('test.exception-handler.latest-handler'));
        }]);

        parallel([function () use ($container, $handlers) {
            $exception = new Exception('xxx', 0);

            Context::set(ResponseInterface::class, new Response());

            $dispatcher = new ExceptionHandlerDispatcher($container);
            $dispatcher->dispatch($exception, $handlers);

            $this->assertSame(BarExceptionHandler::class, Context::get('test.exception-handler.latest-handler'));
        }]);

        parallel([function () use ($container, $handlers) {
            $exception = new Exception('xxx', 500);

            Context::set(ResponseInterface::class, new Response());

            $dispatcher = new ExceptionHandlerDispatcher($container);
            $dispatcher->dispatch($exception, $handlers);

            $this->assertSame(FooExceptionHandler::class, Context::get('test.exception-handler.latest-handler'));
        }]);
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->andReturn(true);
        $container->shouldReceive('get')->with(BarExceptionHandler::class)->andReturn(new BarExceptionHandler());
        $container->shouldReceive('get')->with(FooExceptionHandler::class)->andReturn(new FooExceptionHandler());

        return $container;
    }
}
