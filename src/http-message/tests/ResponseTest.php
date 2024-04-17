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

use Hyperf\Codec\Json;
use Hyperf\Engine\Http\WritableConnection;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Server\Response;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpMessage\Uri\Uri;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Swoole\Http\Response as SwooleResponse;
use Swow\Psr7\Message\ResponsePlusInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
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
        $response->setConnection(new WritableConnection($swooleResponse));
        $status = $response->write($content);
        $this->assertTrue($status);
    }

    public function testToResponseString()
    {
        $response = $this->newResponse();
        if (! $response instanceof ResponsePlusInterface) {
            $this->markTestSkipped('Don\'t assert response which not instanceof ResponsePlusInterface');
        }

        $response->setStatus(200)->setHeaders(['Content-Type' => 'application/json'])->setBody(new SwooleStream(Json::encode(['id' => $id = uniqid()])));
        $this->assertEquals("HTTP/1.1 200 OK\r
Content-Type: application/json\r
Connection: close\r
Content-Length: 22\r
\r
{\"id\":\"" . $id . '"}', $response->toString());
        $this->assertSame("HTTP/1.1 200 OK\r
Content-Type: application/json\r
Connection: close\r
Content-Length: 22\r
\r
", $response->toString(true));
    }

    public function testToRequestString()
    {
        $request = new Request('GET', new Uri('https://www.baidu.com/'), body: 'q=Hyperf');

        $this->assertSame("GET / HTTP/1.1\r
host: www.baidu.com\r
Connection: close\r
Content-Length: 8\r
\r
q=Hyperf", $request->toString());

        $this->assertSame("GET / HTTP/1.1\r
host: www.baidu.com\r
Connection: close\r
Content-Length: 8\r
\r
", $request->toString(true));
    }

    protected function newResponse()
    {
        return new Response();
    }
}
