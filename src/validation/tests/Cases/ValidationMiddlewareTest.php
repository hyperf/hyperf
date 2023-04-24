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
namespace HyperfTest\Validation\Cases;

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\NormalizerInterface;
use Hyperf\Contract\ValidatorInterface;
use Hyperf\Di\ClosureDefinitionCollectorInterface;
use Hyperf\Di\Container;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\MethodDefinitionCollectorInterface;
use Hyperf\Dispatcher\HttpRequestHandler;
use Hyperf\HttpMessage\Base\Response;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\HttpServer\CoreMiddleware;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServer\Router\DispatcherFactory;
use Hyperf\Serializer\SimpleNormalizer;
use Hyperf\Translation\ArrayLoader;
use Hyperf\Translation\Translator;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Middleware\ValidationMiddleware;
use Hyperf\Validation\ValidatorFactory;
use HyperfTest\Validation\Cases\Stub\DemoController;
use HyperfTest\Validation\Cases\Stub\DemoRequest;
use HyperfTest\Validation\Cases\Stub\FooMiddleware;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal
 * @coversNothing
 */
class ValidationMiddlewareTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Context::set(DemoRequest::class . ':' . ValidatorInterface::class, null);
        Context::set('test.validation.DemoRequest.number', 0);
        Context::set('http.request.parsedData', null);
    }

    public function testValidationMiddlewareGetTheLatestInputData()
    {
        $container = $this->createContainer();
        $factory = $container->get(DispatcherFactory::class);

        $router = $factory->getRouter('http');
        $router->addRoute('POST', '/sign-up', 'HyperfTest\Validation\Cases\Stub\DemoController@signUp');

        $dispatcher = $factory->getDispatcher('http');
        $fooMiddleware = new FooMiddleware();
        $middleware = new ValidationMiddleware($container);
        $coreMiddleware = new CoreMiddleware($container, 'http');
        $handler = new HttpRequestHandler([$fooMiddleware, $middleware], $coreMiddleware, $container);
        Context::set(ResponseInterface::class, new Response());

        $request = (new Request('POST', new Uri('/sign-up')))
            ->withParsedBody(['username' => 'Hyperf']);
        $routes = $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());
        $request = Context::set(ServerRequestInterface::class, $request->withAttribute(Dispatched::class, new Dispatched($routes)));

        $response = $handler->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProcess()
    {
        $container = $this->createContainer();
        $factory = $container->get(DispatcherFactory::class);

        $router = $factory->getRouter('http');
        $router->addRoute('POST', '/sign-up', 'HyperfTest\Validation\Cases\Stub\DemoController@signUp');
        $router->addRoute('POST', '/sign-in', 'HyperfTest\Validation\Cases\Stub\DemoController::signIn');
        $router->addRoute('POST', '/sign-out', [\HyperfTest\Validation\Cases\Stub\DemoController::class, 'signOut']);
        $router->addRoute('POST', '/info/{id:\d}', 'HyperfTest\Validation\Cases\Stub\DemoController::info');

        $dispatcher = $factory->getDispatcher('http');
        $middleware = new ValidationMiddleware($container);
        $coreMiddleware = new CoreMiddleware($container, 'http');
        $handler = new HttpRequestHandler([$middleware], $coreMiddleware, $container);
        Context::set(ResponseInterface::class, new Response());

        $request = (new Request('POST', new Uri('/sign-up')))
            ->withParsedBody(['username' => 'Hyperf', 'password' => 'Hyperf']);
        $routes = $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());
        $request = Context::set(ServerRequestInterface::class, $request->withAttribute(Dispatched::class, new Dispatched($routes)));
        $response = $middleware->process($request, $handler);
        $this->assertEquals(200, $response->getStatusCode());

        $request = (new Request('POST', new Uri('/sign-in')))
            ->withParsedBody(['username' => 'Hyperf', 'password' => 'Hyperf']);
        $routes = $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());
        $request = Context::set(ServerRequestInterface::class, $request->withAttribute(Dispatched::class, new Dispatched($routes)));
        $response = $middleware->process($request, $handler);
        $this->assertEquals(200, $response->getStatusCode());

        $request = (new Request('POST', new Uri('/sign-out')))
            ->withParsedBody(['username' => 'Hyperf', 'password' => 'Hyperf']);
        $routes = $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());
        $request = Context::set(ServerRequestInterface::class, $request->withAttribute(Dispatched::class, new Dispatched($routes)));
        $response = $middleware->process($request, $handler);
        $this->assertEquals(200, $response->getStatusCode());

        $request = (new Request('POST', new Uri('/info/1')))
            ->withParsedBody(['username' => 'Hyperf', 'password' => 'Hyperf']);
        $routes = $dispatcher->dispatch($request->getMethod(), $request->getUri()->getPath());
        $request = Context::set(ServerRequestInterface::class, $request->withAttribute(Dispatched::class, new Dispatched($routes)));
        $response = $middleware->process($request, $handler);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"id":1,"request":{"username":"Hyperf","password":"Hyperf"}}', (string) $response->getBody());
    }

    public function testGetValidatorInstance()
    {
        $container = $this->createContainer();
        Context::set(ServerRequestInterface::class, new Request('POST', new Uri('/')));
        $request = $container->get(DemoRequest::class);

        $request->validated();
        $this->assertSame(1, Context::get('test.validation.DemoRequest.number'));

        $request->validated();
        $this->assertSame(1, Context::get('test.validation.DemoRequest.number'));
    }

    public function createContainer()
    {
        $eventDispatcher = Mockery::mock(EventDispatcherInterface::class);
        $container = Mockery::mock(Container::class);

        $container->shouldReceive('get')->with(DispatcherFactory::class)
            ->andReturn(new DispatcherFactory());
        $container->shouldReceive('get')->with(EventDispatcherInterface::class)
            ->andReturn($eventDispatcher);
        $container->shouldReceive('get')->with(ValidatorFactoryInterface::class)
            ->andReturn(new ValidatorFactory(new Translator(new ArrayLoader(), 'en'), $container));
        $container->shouldReceive('get')->with(NormalizerInterface::class)
            ->andReturn(new SimpleNormalizer());
        $container->shouldReceive('get')->with(MethodDefinitionCollectorInterface::class)
            ->andReturn(new MethodDefinitionCollector());
        $container->shouldReceive('get')->with(DemoController::class)
            ->andReturn(new DemoController());
        $container->shouldReceive('get')->with(DemoRequest::class)
            ->andReturn(new DemoRequest($container));
        $container->shouldReceive('has')->with(DemoRequest::class)
            ->andReturn(true);
        $container->shouldReceive('has')->with(ClosureDefinitionCollectorInterface::class)
            ->andReturn(false);
        ApplicationContext::setContainer($container);

        return $container;
    }
}
