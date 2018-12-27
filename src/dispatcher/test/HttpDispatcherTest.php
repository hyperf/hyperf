<?php

namespace HyperflexTest\Dispatcher;


use App\Middlewares\TestMiddleware;
use Hyperflex\Dispatcher\HttpDispatcher;
use Hyperflex\Utils\Context;
use HyperflexTest\Dispatcher\Middlewares\CoreMiddleware;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ProphecyInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @property ProphecyInterface container
 * @property ProphecyInterface request
 * @property ProphecyInterface response
 */
class HttpDispatcherTest extends TestCase
{

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $this->response = $this->prophesize(ResponseInterface::class)->reveal();
        Context::set(ServerRequestInterface::class, $this->request);
        Context::set(ResponseInterface::class, $this->response);
    }

    public function testA()
    {
        $this->container->get(TestMiddleware::class)->willReturn(new TestMiddleware());
        $this->container->get(CoreMiddleware::class)->willReturn(new CoreMiddleware());
        $middlewares = [
            TestMiddleware::class,
        ];
        $coreHandler = CoreMiddleware::class;
        $dispatcher = new HttpDispatcher($middlewares, $coreHandler, $this->container->reveal());
        $this->assertInstanceOf(HttpDispatcher::class, $dispatcher);
        $response = $dispatcher->dispatch($this->request, $this->response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

}