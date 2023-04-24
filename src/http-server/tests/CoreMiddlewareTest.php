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
namespace HyperfTest\HttpServer;

use FastRoute\Dispatcher;
use Hyperf\Context\Context;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Di\ClosureDefinitionCollector;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\Dispatcher\HttpRequestHandler;
use Hyperf\HttpMessage\Exception\ServerErrorHttpException;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\HttpServer\CoreMiddleware;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\HttpServer\Router\Handler;
use Hyperf\Serializer\SimpleNormalizer;
use HyperfTest\HttpServer\Stub\CoreMiddlewareStub;
use HyperfTest\HttpServer\Stub\DemoController;
use HyperfTest\HttpServer\Stub\FooController;
use HyperfTest\HttpServer\Stub\SetHeaderMiddleware;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use ReflectionMethod;

/**
 * @internal
 * @coversNothing
 */
class CoreMiddlewareTest extends TestCase
{
    public function testParseParameters()
    {
        $middleware = new CoreMiddlewareStub($container = $this->getContainer(), 'http');
        $id = rand(0, 99999);

        $params = $middleware->parseMethodParameters(DemoController::class, 'index', ['id' => $id]);

        $this->assertSame([$id, 'Hyperf', []], $params);
    }

    public function testTransferToResponse()
    {
        $middleware = new CoreMiddlewareStub($container = $this->getContainer(), 'http');
        $reflectionMethod = new ReflectionMethod(CoreMiddleware::class, 'transferToResponse');
        $reflectionMethod->setAccessible(true);
        $request = Mockery::mock(ServerRequestInterface::class);
        /** @var ResponseInterface $response */

        // String
        $response = $reflectionMethod->invoke($middleware, $body = 'foo', $request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame($body, (string) $response->getBody());
        $this->assertSame('text/plain', $response->getHeaderLine('content-type'));

        // Array
        $response = $reflectionMethod->invoke($middleware, $body = ['foo' => 'bar'], $request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(json_encode($body), (string) $response->getBody());
        $this->assertSame('application/json', $response->getHeaderLine('content-type'));

        // Arrayable
        $response = $reflectionMethod->invoke($middleware, new class() implements Arrayable {
            public function toArray(): array
            {
                return ['foo' => 'bar'];
            }
        }, $request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(json_encode(['foo' => 'bar']), (string) $response->getBody());
        $this->assertSame('application/json', $response->getHeaderLine('content-type'));

        // Jsonable
        $response = $reflectionMethod->invoke($middleware, new class() implements Jsonable {
            public function __toString(): string
            {
                return json_encode(['foo' => 'bar'], JSON_UNESCAPED_UNICODE);
            }
        }, $request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(json_encode(['foo' => 'bar']), (string) $response->getBody());
        $this->assertSame('application/json', $response->getHeaderLine('content-type'));

        // __toString
        $response = $reflectionMethod->invoke($middleware, new class() {
            public function __toString(): string
            {
                return 'This is a string';
            }
        }, $request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame('This is a string', (string) $response->getBody());
        $this->assertSame('text/plain', $response->getHeaderLine('content-type'));

        // Json encode failed
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Type is not supported');
        $response = $reflectionMethod->invoke($middleware, ['id' => fopen(BASE_PATH . '/.gitignore', 'r+')], $request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testDispatch()
    {
        $container = $this->getContainer();

        $router = $container->get(DispatcherFactory::class)->getRouter('http');
        $router->addRoute('GET', '/user', 'UserController::index');
        $router->addRoute('GET', '/user/{id:\d+}', 'UserController::info');

        $middleware = new CoreMiddleware($container, 'http');

        $request = new Request('GET', new Uri('/user'));
        $request = $middleware->dispatch($request);
        $dispatched = $request->getAttribute(Dispatched::class);
        $this->assertInstanceOf(Request::class, $request);
        $this->assertInstanceOf(Dispatched::class, $dispatched);
        $this->assertInstanceOf(Handler::class, $dispatched->handler);
        $this->assertSame($dispatched, $request->getAttribute(Dispatched::class));
        $this->assertSame('/user', $dispatched->handler->route);
        $this->assertSame('UserController::index', $dispatched->handler->callback);
        $this->assertTrue($dispatched->isFound());

        $request = new Request('GET', new Uri('/user/123'));
        $request = $middleware->dispatch($request);
        $dispatched = $request->getAttribute(Dispatched::class);
        $this->assertInstanceOf(Request::class, $request);
        $this->assertInstanceOf(Dispatched::class, $dispatched);
        $this->assertInstanceOf(Handler::class, $dispatched->handler);
        $this->assertSame($dispatched, $request->getAttribute(Dispatched::class));
        $this->assertSame('/user/{id:\d+}', $dispatched->handler->route);
        $this->assertSame('UserController::info', $dispatched->handler->callback);
        $this->assertTrue($dispatched->isFound());

        $request = new Request('GET', new Uri('/users'));
        $request = $middleware->dispatch($request);
        $dispatched = $request->getAttribute(Dispatched::class);
        $this->assertInstanceOf(Request::class, $request);
        $this->assertInstanceOf(Dispatched::class, $dispatched);
        $this->assertSame($dispatched, $request->getAttribute(Dispatched::class));
        $this->assertFalse($dispatched->isFound());
    }

    public function testProcess()
    {
        $container = $this->getContainer();
        $container->shouldReceive('get')->with(SetHeaderMiddleware::class)->andReturn(new SetHeaderMiddleware($id = uniqid()));

        $router = $container->get(DispatcherFactory::class)->getRouter('http');
        $router->addRoute('GET', '/request', function () {
            return Context::get(ServerRequestInterface::class)->getHeaders();
        });

        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('withAddedHeader')->andReturn($response);
        $response->shouldReceive('withBody')->with(Mockery::any())->andReturnUsing(function ($stream) use ($response, $id) {
            $this->assertInstanceOf(SwooleStream::class, $stream);
            /* @var SwooleStream $stream */
            $this->assertSame(json_encode(['DEBUG' => [$id]]), (string) $stream);
            return $response;
        });
        $request = new Request('GET', new Uri('/request'));
        Context::set(ResponseInterface::class, $response);
        Context::set(ServerRequestInterface::class, $request);

        $middleware = new CoreMiddleware($container, 'http');
        $request = $middleware->dispatch($request);
        $handler = new HttpRequestHandler([SetHeaderMiddleware::class], $middleware, $container);
        $response = $handler->handle($request);
    }

    public function testHandleFound()
    {
        $container = $this->getContainer();
        $container->shouldReceive('get')->with(DemoController::class)->andReturn(new DemoController());
        $middleware = new CoreMiddleware($container, 'http');
        $ref = new ReflectionClass($middleware);
        $method = $ref->getMethod('handleFound');
        $method->setAccessible(true);

        $handler = new Handler([DemoController::class, 'demo'], '/');
        $dispatched = new Dispatched([Dispatcher::FOUND, $handler, []]);
        $res = $method->invokeArgs($middleware, [$dispatched, Mockery::mock(ServerRequestInterface::class)]);
        $this->assertSame('Hello World.', $res);
    }

    public function testHandleFoundWithInvokable()
    {
        $container = $this->getContainer();
        $container->shouldReceive('get')->with(DemoController::class)->andReturn(new DemoController());
        $middleware = new CoreMiddleware($container, 'http');
        $ref = new ReflectionClass($middleware);
        $method = $ref->getMethod('handleFound');
        $method->setAccessible(true);

        $handler = new Handler(DemoController::class, '/');
        $dispatched = new Dispatched([Dispatcher::FOUND, $handler, []]);
        $res = $method->invokeArgs($middleware, [$dispatched, Mockery::mock(ServerRequestInterface::class)]);
        $this->assertSame('Action for an invokable controller.', $res);
    }

    public function testHandleFoundWithNamespace()
    {
        $container = $this->getContainer();
        $container->shouldReceive('get')->with(DemoController::class)->andReturn(new FooController());
        $middleware = new CoreMiddleware($container, 'http');
        $ref = new ReflectionClass($middleware);
        $method = $ref->getMethod('handleFound');
        $method->setAccessible(true);

        $this->expectException(ServerErrorHttpException::class);
        $this->expectExceptionMessage('Method of class does not exist.');
        $handler = new Handler([DemoController::class, 'demo'], '/');
        $dispatched = new Dispatched([Dispatcher::FOUND, $handler, []]);
        $method->invokeArgs($middleware, [$dispatched, Mockery::mock(ServerRequestInterface::class)]);
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
