<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\HttpMessage;

use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Codec\Json;
use HyperfTest\HttpMessage\Stub\Server\RequestStub;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Swoole\Http\Request as SwooleRequest;

/**
 * @internal
 * @coversNothing
 */
class ServerRequestTest extends TestCase
{
    public function testNormalizeParsedBody()
    {
        $data = ['id' => 1];
        $json = ['name' => 'Hyperf'];

        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('');

        $this->assertSame($data, RequestStub::normalizeParsedBody($data));
        $this->assertSame($data, RequestStub::normalizeParsedBody($data, $request));

        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('application/xml');
        $this->assertSame($data, RequestStub::normalizeParsedBody($data, $request));

        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('application/json; charset=utf-8');
        $request->shouldReceive('getBody')->andReturn(new SwooleStream(json_encode($json)));
        $this->assertSame($json, RequestStub::normalizeParsedBody($data, $request));

        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('application/json; charset=utf-8');
        $request->shouldReceive('getBody')->andReturn(new SwooleStream('xxxx'));
        $this->assertSame([], RequestStub::normalizeParsedBody($data, $request));
    }

    public function testNormalizeParsedBodyInvalidContentType()
    {
        $data = ['id' => 1];
        $json = ['name' => 'Hyperf'];

        $request = Mockery::mock(RequestInterface::class);
        $request->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('application/JSON');
        $request->shouldReceive('getBody')->andReturn(new SwooleStream(json_encode($json)));
        $this->assertSame($json, RequestStub::normalizeParsedBody($data, $request));
    }

    public function testGetUriFromGlobals()
    {
        $swooleRequest = Mockery::mock(SwooleRequest::class);
        $data = ['name' => 'Hyperf'];
        $swooleRequest->shouldReceive('rawContent')->andReturn(Json::encode($data));
        $swooleRequest->server = ['server_port' => 9501];
        $request = Request::loadFromSwooleRequest($swooleRequest);
        $uri = $request->getUri();
        $this->assertSame(9501, $uri->getPort());

        $swooleRequest = Mockery::mock(SwooleRequest::class);
        $data = ['name' => 'Hyperf'];
        $swooleRequest->shouldReceive('rawContent')->andReturn(Json::encode($data));
        $swooleRequest->header = ['host' => '127.0.0.1'];
        $swooleRequest->server = ['server_port' => 9501];
        $request = Request::loadFromSwooleRequest($swooleRequest);
        $uri = $request->getUri();
        $this->assertSame(null, $uri->getPort());
    }
}
