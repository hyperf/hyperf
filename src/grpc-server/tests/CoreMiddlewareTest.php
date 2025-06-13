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
use Hyperf\Config\Config;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Di\ClosureDefinitionCollector;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\Grpc\PathGenerator;
use Hyperf\GrpcServer\CoreMiddleware;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Handler;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\RpcServer\Router\DispatcherFactory as RPCDispatcherFactory;
use Hyperf\RpcServer\Router\RouteCollector;
use Hyperf\Serializer\SimpleNormalizer;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class CoreMiddlewareTest extends TestCase
{
    public function testGRPCCoreMiddlewareDispatch()
    {
        $container = $this->getContainer();

        /** @var RouteCollector $router */
        $router = $container->get(RPCDispatcherFactory::class . '.unit')->getRouter('grpc');
        $router->addRoute('/users', static function () {});

        $middleware = new CoreMiddleware($container, 'grpc');

        $request = new Request('POST', new Uri('/users'));
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
        ApplicationContext::setContainer($container);
        $container->shouldReceive('get')->with(DispatcherFactory::class)->andReturn(new DispatcherFactory());
        $container->shouldReceive('get')->with(MethodDefinitionCollectorInterface::class)->andReturn(new MethodDefinitionCollector());
        $container->shouldReceive('has')->with(ClosureDefinitionCollectorInterface::class)->andReturn(false);
        $container->shouldReceive('get')->with(ClosureDefinitionCollectorInterface::class)->andReturn(new ClosureDefinitionCollector());
        $container->shouldReceive('get')->with(NormalizerInterface::class)->andReturn(new SimpleNormalizer());
        $container->shouldReceive('get')->with(ProtocolManager::class)->andReturn($manager = new ProtocolManager(new Config([])));
        $manager->registerOrAppend('grpc', [
            'path-generator' => PathGenerator::class,
        ]);
        $container->shouldReceive('has')->with(PathGenerator::class)->andReturnTrue();
        $container->shouldReceive('get')->with(PathGenerator::class)->andReturn(new PathGenerator());
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)->andReturn(Mockery::mock(EventDispatcherInterface::class));
        $container->shouldReceive('make')->with(RPCDispatcherFactory::class)->withAnyArgs()->andReturn($dispatcher = new RPCDispatcherFactory(Mockery::mock(EventDispatcherInterface::class), new PathGenerator()));
        $container->shouldReceive('get')->with(RPCDispatcherFactory::class . '.unit')->andReturn($dispatcher);
        $container->shouldReceive('get')->with(ConfigInterface::class)->andReturn(new Config(['grpc_server' => ['rpc' => ['grpc' => ['enable' => true]]]]));
        return $container;
    }
}
