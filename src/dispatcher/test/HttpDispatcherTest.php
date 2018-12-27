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
