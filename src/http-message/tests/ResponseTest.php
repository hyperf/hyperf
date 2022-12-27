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
namespace HyperfTest\HttpMessage;

use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Server\Connection\SwooleConnection;
use Hyperf\HttpMessage\Server\Response;
use Mockery;
use PHPUnit\Framework\TestCase;
use Swoole\Http\Response as SwooleResponse;

/**
 * @internal
 * @coversNothing
 */
class ResponseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testStatusCode()
    {
        $response = $this->newResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(201, $response->withStatus(201)->getStatusCode());
    }

    public function testHeaders()
    {
        $response = $this->newResponse();
        $this->assertSame([], $response->getHeaders());
        $response = $response->withHeader('Server', 'Hyperf');
        $this->assertSame(['Server' => ['Hyperf']], $response->getHeaders());
        $this->assertSame(['Hyperf'], $response->getHeader('Server'));
        $this->assertSame('Hyperf', $response->getHeaderLine('Server'));
    }

    public function testCookies()
    {
        $cookie = new Cookie('test', uniqid(), 3600, '/', 'hyperf.io');
        $response = $this->newResponse();
        $this->assertSame([], $response->getCookies());
        $response = $response->withCookie($cookie);
        $this->assertSame(['hyperf.io' => ['/' => ['test' => $cookie]]], $response->getCookies());
    }

    public function testWrite()
    {
        $content = 'hello';
        $swooleResponse = Mockery::mock(SwooleResponse::class);
        $swooleResponse->shouldReceive('write')->with($content)->once()->andReturn(true);

        $response = $this->newResponse();
        $response->setConnection(new SwooleConnection($swooleResponse));
        $status = $response->write($content);
        $this->assertTrue($status);
    }

    protected function newResponse()
    {
        return new Response();
    }
}
