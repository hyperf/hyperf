<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Session;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\SessionInterface;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\Session\Handler\FileHandler;
use Hyperf\Session\Handler\HandlerManager;
use Hyperf\Session\Middleware\SessionMiddleware;
use Hyperf\Session\Session;
use Hyperf\Session\SessionManager;
use Hyperf\Utils\Context;
use Hyperf\Utils\Filesystem\Filesystem;
use HyperfTest\Session\Stub\FooHandler;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @internal
 * @covers \Hyperf\Session\Middleware\SessionMiddleware
 * @covers \Hyperf\Session\SessionManager
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
        $sessionManager = new SessionManager($container, $config);
        $middleware = new SessionMiddleware($sessionManager, $config);
        $time = time();
        $response = $middleware->process($request, $requestHandler);

        /** @var Cookie $cookie */
        $cookie = $response->getCookies()['']['/'][$session->getName()];
        $this->assertSame($time + (5 * 60 * 60), $cookie->getExpiresTime());
    }


}
