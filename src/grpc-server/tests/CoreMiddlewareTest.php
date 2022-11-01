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
namespace HyperfTest\GrpcServer;

use Closure;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Di\ClosureDefinitionCollector;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\GrpcServer\CoreMiddleware;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Handler;
use Hyperf\Utils\Serializer\SimpleNormalizer;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class CoreMiddlewareTest extends TestCase
{
    public function testDispatch()
    {
        $container = $this->getContainer();

        $router = $container->get(DispatcherFactory::class)->getRouter('grpc');
        $router->addRoute('GET', '/users', function () {});

        $middleware = new CoreMiddleware($container, 'grpc');

        $request = new Request('GET', new Uri('/users'));
        $request = $middleware->dispatch($request);
        $dispatched = $request->getAttribute(Dispatched::class);
        $this->assertInstanceOf(Request::class, $request);
        $this->assertInstanceOf(Dispatched::class, $dispatched);
        $this->assertInstanceOf(Handler::class, $dispatched->handler);
        $this->assertInstanceOf(Closure::class, $dispatched->handler->callback);
        $this->assertSame($dispatched, $request->getAttribute(Dispatched::class));
        $this->assertTrue($dispatched->isFound());
    }

    protected function getContainer()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(DispatcherFactory::class)->andReturn(new DispatcherFactory());
        $container->shouldReceive('get')->with(MethodDefinitionCollectorInterface::class)
            ->andReturn(new MethodDefinitionCollector());
        $container->shouldReceive('has')->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);
        $container->shouldReceive('get')->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(new ClosureDefinitionCollector());
        $container->shouldReceive('get')->with(NormalizerInterface::class)
            ->andReturn(new SimpleNormalizer());
        return $container;
    }
}
