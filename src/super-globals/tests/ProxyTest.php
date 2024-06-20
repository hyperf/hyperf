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

namespace HyperfTest\SuperGlobals;

use Hyperf\Context\Context;
use Hyperf\Context\RequestContext;
use Hyperf\Contract\SessionInterface;
use Hyperf\Coroutine\Waiter;
use Hyperf\SuperGlobals\Proxy\Cookie;
use Hyperf\SuperGlobals\Proxy\File;
use Hyperf\SuperGlobals\Proxy\Get;
use Hyperf\SuperGlobals\Proxy\Post;
use Hyperf\SuperGlobals\Proxy\Request;
use Hyperf\SuperGlobals\Proxy\Server;
use Hyperf\SuperGlobals\Proxy\Session;
use HyperfTest\SuperGlobals\Stub\ContainerStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Swow\Psr7\Message\ServerRequestPlusInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ProxyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Context::set(ServerRequestInterface::class, null);
        Context::set('http.request.parsedData', null);
    }

    public function testCookie()
    {
        $request = Mockery::mock(ServerRequestPlusInterface::class);
        $request->shouldReceive('getCookieParams')->andReturn([
            'id' => $id = uniqid(),
        ]);
        RequestContext::set($request);
        $proxy = new Cookie();

        $this->assertSame($id, $proxy['id']);
    }

    public function testFile()
    {
        $request = RequestContext::set(Mockery::mock(ServerRequestPlusInterface::class));
        $request->shouldReceive('getUploadedFiles')->andReturn([
            'file' => $file = Mockery::mock(UploadedFileInterface::class),
        ]);
        $proxy = new File();

        $this->assertSame($file, $proxy['file']);
    }

    public function testGet()
    {
        $request = RequestContext::set(Mockery::mock(ServerRequestPlusInterface::class));
        $request->shouldReceive('getQueryParams')->andReturn([
            'id' => $id = uniqid(),
        ]);
        $proxy = new Get();

        $this->assertSame($id, $proxy['id']);
    }

    public function testPost()
    {
        $request = RequestContext::set(Mockery::mock(ServerRequestPlusInterface::class));
        $request->shouldReceive('getParsedBody')->andReturn([
            'id' => $id = uniqid(),
        ]);
        $proxy = new Post();

        $this->assertSame($id, $proxy['id']);
    }

    public function testRequest()
    {
        ContainerStub::getContainer();

        $request = RequestContext::set(Mockery::mock(ServerRequestPlusInterface::class));
        $request->shouldReceive('getQueryParams')->andReturn([
            'id' => $id = '0' . uniqid(),
        ]);
        $request->shouldReceive('getParsedBody')->andReturn([
            'name' => $name = 'Hyperf' . uniqid(),
        ]);
        $proxy = new Request();

        $this->assertSame($id, $proxy['id']);
        $this->assertSame($name, $proxy['name']);
    }

    public function testServer()
    {
        $request = RequestContext::set(Mockery::mock(ServerRequestPlusInterface::class));
        $request->shouldReceive('getServerParams')->andReturn([
            'server_name' => $name = 'Server.' . uniqid(),
        ]);
        $request->shouldReceive('getHeaders')->andReturn([
            'X-Token' => $token = uniqid(),
            'host' => ['hyperf.io'],
            'x-forwarded-for' => ['127.0.0.1'],
        ]);
        $proxy = new Server([]);

        $this->assertSame($name, $proxy['SERVER_NAME']);
        $this->assertSame($token, $proxy['HTTP_X_TOKEN']);
        $this->assertSame('127.0.0.1', $proxy['HTTP_X_FORWARDED_FOR']);
        $this->assertSame('hyperf.io', $proxy['HTTP_HOST']);

        $proxy = new Server($proxy);

        $this->assertSame($name, $proxy['SERVER_NAME']);
        $this->assertSame($token, $proxy['HTTP_X_TOKEN']);
        $this->assertSame('127.0.0.1', $proxy['HTTP_X_FORWARDED_FOR']);
        $this->assertSame('hyperf.io', $proxy['HTTP_HOST']);

        (new Waiter())->wait(function () {
            $proxy = new Server([]);
            $this->assertSame([], $proxy->toArray());
            $this->assertSame(null, $proxy['SERVER_NAME'] ?? null);
        });
    }

    public function testSession()
    {
        $container = ContainerStub::getContainer();
        $id = uniqid();
        $container->shouldReceive('get')->with(SessionInterface::class)->andReturnUsing(function () use ($id) {
            $session = Mockery::mock(SessionInterface::class);
            $session->shouldReceive('get')->with('id')->andReturn($id);
            return $session;
        });

        $proxy = new Session();
        $this->assertSame($id, $proxy['id']);
    }
}
