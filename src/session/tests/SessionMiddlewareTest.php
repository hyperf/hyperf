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

namespace HyperfTest\Session;

use Carbon\Carbon;
use Hyperf\Config\Config;
use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\SessionInterface;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\HttpMessage\Uri\Uri;
use Hyperf\Session\Handler\FileHandler;
use Hyperf\Session\Middleware\SessionMiddleware;
use Hyperf\Session\Session;
use Hyperf\Session\SessionManager;
use Hyperf\Stringable\Str;
use Hyperf\Support\Filesystem\Filesystem;
use HyperfTest\Session\Stub\FooHandler;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use SessionHandlerInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversClass(SessionMiddleware::class)]
#[CoversClass(SessionManager::class)]
/**
 * @internal
 * @coversNothing
 */
class SessionMiddlewareTest extends TestCase
{
    public function testHandle()
    {
        $request = new Request('GET', '/test');
        $request = $request->withCookieParams(['HYPERF_SESSION_ID' => 'foo123']);
        $requestHandler = Mockery::mock(RequestHandlerInterface::class);
        $requestHandler->shouldReceive('handle')->andReturn(new Response());

        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(SessionInterface::class)->andReturnTrue();
        $container->shouldReceive('has')->with(FileHandler::class)->andReturnTrue();
        $fileHandler = new FileHandler(new Filesystem(), '/tmp', 10);
        $container->shouldReceive('get')->with(FileHandler::class)->andReturn($fileHandler);
        /** @var Session $session */
        $session = new Session('HYPERF_SESSION_ID', $fileHandler);
        $container->shouldReceive('get')->with(SessionInterface::class)->andReturn($session);
        $container->shouldReceive('get')->with(FooHandler::class)->andReturn(new FooHandler());

        $config = Mockery::mock(ConfigInterface::class);
        $config->shouldReceive('get')->with('session.handler')->andReturn(FooHandler::class);
        $config->shouldReceive('has')->with('session.handler')->andReturn(true);
        $config->shouldReceive('get')->with('session.options.expire_on_close')->andReturn(1);
        $config->shouldReceive('get')->with('session.options.session_name', 'HYPERF_SESSION_ID')->andReturn('HYPERF_SESSION_ID');
        $config->shouldReceive('get')->with('session.options.domain')->andReturn(null);
        $config->shouldReceive('get')->with('session.options.cookie_lifetime', 5 * 60)->andReturn(5 * 60);
        $config->shouldReceive('get')->with('session.options.cookie_same_site')->andReturn(null);

        $sessionManager = new SessionManager($container, $config);
        $middleware = new SessionMiddleware($sessionManager, $config);
        $response = $middleware->process($request, $requestHandler);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertInstanceOf(SessionInterface::class, $session = Context::get(SessionInterface::class));
        $this->assertIsString($session->getId());
        $this->assertTrue(ctype_alnum($session->getId()));
        $this->assertSame(40, strlen($session->getId()));
        $this->assertArrayHasKey('', $response->getCookies());
        $this->assertArrayHasKey('/', $response->getCookies()['']);
        $this->assertArrayHasKey($session->getName(), $response->getCookies()['']['/']);
        $this->assertInstanceOf(Cookie::class, $response->getCookies()['']['/'][$session->getName()]);
        /** @var Cookie $cookie */
        $cookie = $response->getCookies()['']['/'][$session->getName()];
        $this->assertSame('HYPERF_SESSION_ID', $cookie->getName());
        $this->assertSame($session->getId(), $cookie->getValue());
        $this->assertSame('/', $cookie->getPath());
        $this->assertSame(0, $cookie->getExpiresTime());
    }

    public function testSessionWithExpiresTime()
    {
        $request = new Request('GET', '/test');
        $request = $request->withCookieParams(['HYPERF_SESSION_ID' => 'foo123']);
        $requestHandler = Mockery::mock(RequestHandlerInterface::class);
        $requestHandler->shouldReceive('handle')->andReturn(new Response());

        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(SessionInterface::class)->andReturnTrue();
        $container->shouldReceive('has')->with(FileHandler::class)->andReturnTrue();
        $fileHandler = new FileHandler(new Filesystem(), '/tmp', 10);
        $container->shouldReceive('get')->with(FileHandler::class)->andReturn($fileHandler);
        /** @var Session $session */
        $session = new Session('HYPERF_SESSION_ID', $fileHandler);
        $container->shouldReceive('get')->with(SessionInterface::class)->andReturn($session);
        $container->shouldReceive('get')->with(FooHandler::class)->andReturn(new FooHandler());

        $config = Mockery::mock(ConfigInterface::class);
        $config->shouldReceive('get')->with('session.handler')->andReturn(FooHandler::class);
        $config->shouldReceive('has')->with('session.handler')->andReturn(true);
        $config->shouldReceive('get')->with('session.options.expire_on_close')->andReturn(0);
        $config->shouldReceive('get')->with('session.options.session_name', 'HYPERF_SESSION_ID')->andReturn('HYPERF_SESSION_ID');
        $config->shouldReceive('get')->with('session.options.domain')->andReturn(null);
        $config->shouldReceive('get')->with('session.options.cookie_lifetime', 5 * 60 * 60)->andReturn(10 * 60 * 60);
        $config->shouldReceive('get')->with('session.options.cookie_same_site')->andReturn(null);

        $sessionManager = new SessionManager($container, $config);
        $middleware = new SessionMiddleware($sessionManager, $config);
        $time = time();
        Carbon::setTestNow(Carbon::createFromTimestampUTC($time));
        $response = $middleware->process($request, $requestHandler);
        Carbon::setTestNow();

        /** @var Cookie $cookie */
        $cookie = $response->getCookies()['']['/'][$session->getName()];
        $this->assertSame($time + (10 * 60 * 60), $cookie->getExpiresTime());
    }

    public function testSessionOptionsDomain()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(SessionInterface::class)->andReturnTrue();

        $config = new Config([
            'session' => [
                'handler' => FileHandler::class,
                'options' => [
                    'connection' => 'default',
                    'path' => BASE_PATH . '/runtime/session',
                    'gc_maxlifetime' => 1200,
                    'session_name' => 'HYPERF_SESSION_ID',
                    'cookie_lifetime' => 5 * 60 * 60,
                ],
            ],
        ]);

        $middleware = new SessionMiddleware(Mockery::mock(SessionManager::class), $config);
        $ref = new ReflectionClass($middleware);
        $method = $ref->getMethod('addCookieToResponse');

        $request = new Request('GET', new Uri('http://hyperf.io'));
        $session = new Session('test', Mockery::mock(SessionHandlerInterface::class));
        $response = new Response();
        /** @var Response $response */
        $response = $method->invokeArgs($middleware, [$request, $response, $session]);
        $this->assertSame('hyperf.io', $response->getCookies()['hyperf.io']['/']['test']->getDomain());

        $config = new Config([
            'session' => [
                'handler' => FileHandler::class,
                'options' => [
                    'connection' => 'default',
                    'path' => BASE_PATH . '/runtime/session',
                    'gc_maxlifetime' => 1200,
                    'session_name' => 'HYPERF_SESSION_ID',
                    'domain' => null,
                    'cookie_lifetime' => 5 * 60 * 60,
                ],
            ],
        ]);

        $middleware = new SessionMiddleware(Mockery::mock(SessionManager::class), $config);
        $ref = new ReflectionClass($middleware);
        $method = $ref->getMethod('addCookieToResponse');

        $request = new Request('GET', new Uri('http://hyperf.io'));
        $session = new Session('test', Mockery::mock(SessionHandlerInterface::class));
        $response = new Response();
        /** @var Response $response */
        $response = $method->invokeArgs($middleware, [$request, $response, $session]);
        $this->assertSame('hyperf.io', $response->getCookies()['hyperf.io']['/']['test']->getDomain());

        $config = new Config([
            'session' => [
                'handler' => FileHandler::class,
                'options' => [
                    'connection' => 'default',
                    'path' => BASE_PATH . '/runtime/session',
                    'gc_maxlifetime' => 1200,
                    'session_name' => 'HYPERF_SESSION_ID',
                    'domain' => 'hyperf.wiki',
                    'cookie_lifetime' => 5 * 60 * 60,
                ],
            ],
        ]);

        $middleware = new SessionMiddleware(Mockery::mock(SessionManager::class), $config);
        $ref = new ReflectionClass($middleware);
        $method = $ref->getMethod('addCookieToResponse');

        $request = new Request('GET', new Uri('http://hyperf.io'));
        $session = new Session('test', Mockery::mock(SessionHandlerInterface::class));
        $response = new Response();
        /** @var Response $response */
        $response = $method->invokeArgs($middleware, [$request, $response, $session]);
        $this->assertFalse(isset($response->getCookies()['hyperf.io']));
        $this->assertSame('hyperf.wiki', $response->getCookies()['hyperf.wiki']['/']['test']->getDomain());
    }

    public function testSessionStoreCurrentUrl()
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(SessionInterface::class)->andReturnTrue();
        $config = Mockery::mock(ConfigInterface::class);
        $config->shouldReceive('get')->with('session.handler')->andReturn(FooHandler::class);
        $config->shouldReceive('has')->with('session.handler')->andReturn(true);
        $config->shouldReceive('get')->with('session.options.expire_on_close')->andReturn(0);
        $sessionManager = new SessionManager($container, $config);
        $middleware = new SessionMiddleware($sessionManager, $config);
        $reflectionClass = new ReflectionClass(SessionMiddleware::class);
        $reflectionMethod = $reflectionClass->getMethod('fullUrl');
        $result = $reflectionMethod->invokeArgs($middleware, [new Request('get', new Uri($path = '/foo/bar'))]);
        $this->assertSame($path, $result);
        $result = $reflectionMethod->invokeArgs($middleware, [new Request('get', new Uri($path = '/foo/bar?baz=1'))]);
        $this->assertSame($path, $result);
        $result = $reflectionMethod->invokeArgs($middleware, [new Request('get', new Uri($path = '/foo/bar?baz=1&bar=foo'))]);
        $this->assertSame($path, $result);
        $result = $reflectionMethod->invokeArgs($middleware, [new Request('get', new Uri($path = '/foo/bar/'))]);
        $this->assertSame($path, $result);
        $result = $reflectionMethod->invokeArgs($middleware, [new Request('get', new Uri($path = '/foo/bar/?baz=1'))]);
        $this->assertSame($path, $result);
        $result = $reflectionMethod->invokeArgs($middleware, [new Request('get', new Uri($path = '/foo/bar/?baz=1&bar=foo'))]);
        $this->assertSame($path, $result);
    }

    public function testAddCookieToResponse()
    {
        $config = Mockery::mock(ConfigInterface::class);
        $config->shouldReceive('get')->with('session.options.expire_on_close')->andReturn(0);
        $config->shouldReceive('get')->with('session.options.domain')->andReturn(null);
        $config->shouldReceive('get')->with('session.options.cookie_lifetime', 5 * 60 * 60)->andReturn(5 * 60);
        $config->shouldReceive('get')->with('session.options.cookie_same_site')->andReturn(null);

        $middleware = new SessionMiddleware(Mockery::mock(SessionManager::class), $config);
        $ref = new ReflectionClass($middleware);
        $method = $ref->getMethod('addCookieToResponse');
        $request = new Request('GET', new Uri('http://hyperf.io'));
        $session = new Session('test', Mockery::mock(SessionHandlerInterface::class), $id = Str::random(40));
        $response = new Response();
        /** @var Response $response */
        $response = $method->invokeArgs($middleware, [$request, $response, $session]);
        $cookie = $response->getCookies();
        $this->assertSame($id, $response->getCookies()['hyperf.io']['/']['test']->getValue());
        $setCookieString = $response->getCookies()['hyperf.io']['/']['test']->__toString();

        $request = new Request('GET', new Uri('http://hyperf.io'));
        $session = new Session('test', Mockery::mock(SessionHandlerInterface::class), $id);
        $response = Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('withHeader')->once()->andReturnUsing(function ($key, $value) use ($setCookieString, $response) {
            $this->assertSame('Set-Cookie', $key);
            $this->assertSame($setCookieString, $value);
            return $response;
        });
        $method->invokeArgs($middleware, [$request, $response, $session]);
    }
}
