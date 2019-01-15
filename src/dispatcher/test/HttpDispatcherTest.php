<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Dispatcher;

use App\Middlewares\TestMiddleware;
use Hyperf\Dispatcher\HttpDispatcher;
use Hyperf\Utils\Context;
use HyperfTest\Dispatcher\Middlewares\CoreMiddleware;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ProphecyInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swoft\Http\Message\Server\Response;

/**
 * @property ProphecyInterface container
 * @property ProphecyInterface request
 * @property ProphecyInterface response
 */
class HttpDispatcherTest extends TestCase
{
    protected function setUp()
    {
        $this->request = $this->prophesize(ServerRequestInterface::class)->reveal();
        $this->response = $this->prophesize(ResponseInterface::class);
        $swooleResponse = $this->getMockBuilder(\Swoole\Http\Response::class)->getMock();
        $this->response->withAddedHeader('Server', 'Hyperf')
            ->shouldBeCalled()
            ->willReturn((new Response($swooleResponse))->withAddedHeader('Server', 'Hyperf'));
        $this->response = $this->response->reveal();
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(CoreMiddleware::class)->willReturn(new CoreMiddleware());
        $container->get(TestMiddleware::class)->willReturn(new TestMiddleware());
        $this->container = $container->reveal();
        Context::set(ServerRequestInterface::class, $this->request);
        Context::set(ResponseInterface::class, $this->response);
    }

    protected function tearDown()
    {
        Context::destroy();
    }

    public function testA()
    {
        $middlewares = [
            TestMiddleware::class,
        ];
        $coreHandler = $this->container->get(CoreMiddleware::class);
        $dispatcher = new HttpDispatcher($this->container);
        $this->assertInstanceOf(HttpDispatcher::class, $dispatcher);
        $response = $dispatcher->dispatch($this->request, $middlewares, $coreHandler);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('Hyperf', $response->getHeaderLine('Server'));
    }
}
