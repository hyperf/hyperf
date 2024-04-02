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

namespace HyperfTest\Dispatcher;

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Dispatcher\HttpDispatcher;
use Hyperf\HttpMessage\Server\Response;
use HyperfTest\Dispatcher\Middlewares\CoreMiddleware;
use HyperfTest\Dispatcher\Middlewares\Test2Middleware;
use HyperfTest\Dispatcher\Middlewares\TestMiddleware;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class HttpDispatcherTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testDispatch()
    {
        $middlewares = [
            TestMiddleware::class,
        ];
        $container = $this->getContainer();
        $request = Context::get(ServerRequestInterface::class);
        $coreHandler = $container->get(CoreMiddleware::class);
        $dispatcher = new HttpDispatcher($container);
        $this->assertInstanceOf(HttpDispatcher::class, $dispatcher);
        $response = $dispatcher->dispatch($request, $middlewares, $coreHandler);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('Hyperf', $response->getHeaderLine('Server'));
        $this->assertSame('Hyperf', $response->getHeaderLine('Test'));
    }

    public function testRepeatedMiddleware()
    {
        $middlewares = [
            TestMiddleware::class,
            TestMiddleware::class,
        ];
        $container = $this->getContainer();
        $request = Context::get(ServerRequestInterface::class);
        $coreHandler = $container->get(CoreMiddleware::class);
        $dispatcher = new HttpDispatcher($container);
        $this->assertInstanceOf(HttpDispatcher::class, $dispatcher);
        $response = $dispatcher->dispatch($request, $middlewares, $coreHandler);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('Hyperf', $response->getHeaderLine('Server'));
        $this->assertSame('Hyperf, Hyperf', $response->getHeaderLine('Test'));
    }

    public function testIntervalRepeatedMiddleware()
    {
        $middlewares = [
            TestMiddleware::class,
            3 => Test2Middleware::class,
            TestMiddleware::class,
        ];
        $container = $this->getContainer();
        $request = Context::get(ServerRequestInterface::class);
        $coreHandler = $container->get(CoreMiddleware::class);
        $dispatcher = new HttpDispatcher($container);
        $this->assertInstanceOf(HttpDispatcher::class, $dispatcher);
        $response = $dispatcher->dispatch($request, $middlewares, $coreHandler);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('Hyperf', $response->getHeaderLine('Server'));
        $this->assertSame('Hyperf, Hyperf2, Hyperf', $response->getHeaderLine('Test'));
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        ApplicationContext::setContainer($container);

        $container->shouldReceive('get')->with(CoreMiddleware::class)->andReturn(new CoreMiddleware());
        $container->shouldReceive('get')->with(TestMiddleware::class)->andReturn(new TestMiddleware());
        $container->shouldReceive('get')->with(Test2Middleware::class)->andReturn(new Test2Middleware());
        $request = Mockery::mock(ServerRequestInterface::class);
        $response = new Response();
        Context::set(ServerRequestInterface::class, $request);
        Context::set(ResponseInterface::class, $response);
        return $container;
    }
}
