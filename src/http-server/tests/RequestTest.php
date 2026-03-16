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

use Hyperf\Context\Context;
use Hyperf\Context\RequestContext;
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\HttpServer\Request;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Swow\Psr7\Message\ServerRequestPlusInterface;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class RequestTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Context::set(ServerRequestInterface::class, null);
        Context::set('http.request.parsedData', null);
    }

    public function testRequestHasFile()
    {
        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $psrRequest->shouldReceive('getUploadedFiles')->andReturn([
            'file' => new UploadedFile('/tmp/tmp_name', 32, 0),
        ]);
        Context::set(ServerRequestInterface::class, $psrRequest);
        $request = new Request();

        $this->assertTrue($request->hasFile('file'));
        $this->assertFalse($request->hasFile('file2'));
        $this->assertInstanceOf(UploadedFile::class, $request->file('file'));
    }

    public function testRequestHeaderDefaultValue()
    {
        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $psrRequest->shouldReceive('hasHeader')->with('Version')->andReturn(false);
        $psrRequest->shouldReceive('hasHeader')->with('Hyperf-Version')->andReturn(true);
        $psrRequest->shouldReceive('getHeaderLine')->with('Hyperf-Version')->andReturn('v1.0');
        RequestContext::set($psrRequest);

        $psrRequest = new Request();
        $res = $psrRequest->header('Version', 'v1');
        $this->assertSame('v1', $res);

        $res = $psrRequest->header('Hyperf-Version', 'v0');
        $this->assertSame('v1.0', $res);
    }

    public function testRequestInput()
    {
        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $psrRequest->shouldReceive('getParsedBody')->andReturn(['id' => 1]);
        $psrRequest->shouldReceive('getQueryParams')->andReturn([]);
        RequestContext::set($psrRequest);

        $psrRequest = new Request();
        $this->assertSame(1, $psrRequest->input('id'));
        $this->assertSame('Hyperf', $psrRequest->input('name', 'Hyperf'));
    }

    public function testRequestAll()
    {
        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $psrRequest->shouldReceive('getParsedBody')->andReturn(['id' => 1, '123' => '123']);
        $psrRequest->shouldReceive('getQueryParams')->andReturn(['name' => 'Hyperf']);
        RequestContext::set($psrRequest);

        $psrRequest = new Request();
        $this->assertSame(['name' => 'Hyperf', 'id' => 1, 123 => '123'], $psrRequest->all());

        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $psrRequest->shouldReceive('getParsedBody')->andReturn(['name' => 'Hyperf']);
        $psrRequest->shouldReceive('getQueryParams')->andReturn(['id' => 1, '123' => '123']);
        RequestContext::set($psrRequest);

        $psrRequest = new Request();

        $this->assertSame(['name' => 'Hyperf', 'id' => 1, 123 => '123'], $psrRequest->all());
    }

    public function testRequestAllByReplace()
    {
        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $psrRequest->shouldReceive('getParsedBody')->andReturn(['id' => 1, 'data' => ['id' => 2]]);
        $psrRequest->shouldReceive('getQueryParams')->andReturn(['data' => 'Hyperf']);
        RequestContext::set($psrRequest);

        $psrRequest = new Request();
        $this->assertEquals(['id' => 1, 'data' => 'Hyperf'], $psrRequest->all());
    }

    public function testRequestInputs()
    {
        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $psrRequest->shouldReceive('getParsedBody')->andReturn(['id' => 1]);
        $psrRequest->shouldReceive('getQueryParams')->andReturn([]);
        RequestContext::set($psrRequest);

        $psrRequest = new Request();
        $this->assertSame(['id' => 1, 'name' => 'Hyperf'], $psrRequest->inputs(['id', 'name'], ['name' => 'Hyperf']));
    }

    public function testClearStoredParsedData()
    {
        $psrRequest = new \Hyperf\HttpMessage\Server\Request('GET', '/');
        $psrRequest = $psrRequest->withParsedBody(['id' => 1]);
        RequestContext::set($psrRequest);

        $request = new Request();
        $this->assertSame(['id' => 1], $request->all());

        $psrRequest = $psrRequest->withParsedBody(['id' => 1, 'name' => 'hyperf']);
        Context::set(ServerRequestInterface::class, $psrRequest);
        $this->assertSame(['id' => 1], $request->all());

        $request->clearStoredParsedData();
        $this->assertSame(['id' => 1, 'name' => 'hyperf'], $request->all());
    }
}
