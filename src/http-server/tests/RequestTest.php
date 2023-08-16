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
use Hyperf\HttpMessage\Upload\UploadedFile;
use Hyperf\HttpServer\Request;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal
 * @coversNothing
 */
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
        $psrRequest = Mockery::mock(ServerRequestInterface::class);
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
        $psrRequest = Mockery::mock(ServerRequestInterface::class);
        $psrRequest->shouldReceive('hasHeader')->with('Version')->andReturn(false);
        $psrRequest->shouldReceive('hasHeader')->with('Hyperf-Version')->andReturn(true);
        $psrRequest->shouldReceive('getHeaderLine')->with('Hyperf-Version')->andReturn('v1.0');
        Context::set(ServerRequestInterface::class, $psrRequest);

        $psrRequest = new Request();
        $res = $psrRequest->header('Version', 'v1');
        $this->assertSame('v1', $res);

        $res = $psrRequest->header('Hyperf-Version', 'v0');
        $this->assertSame('v1.0', $res);
    }

    public function testRequestInput()
    {
        $psrRequest = Mockery::mock(ServerRequestInterface::class);
        $psrRequest->shouldReceive('getParsedBody')->andReturn(['id' => 1]);
        $psrRequest->shouldReceive('getQueryParams')->andReturn([]);
        Context::set(ServerRequestInterface::class, $psrRequest);

        $psrRequest = new Request();
        $this->assertSame(1, $psrRequest->input('id'));
        $this->assertSame('Hyperf', $psrRequest->input('name', 'Hyperf'));
    }

    public function testRequestAll()
    {
        $psrRequest = Mockery::mock(ServerRequestInterface::class);
        $psrRequest->shouldReceive('getParsedBody')->andReturn(['id' => 1]);
        $psrRequest->shouldReceive('getQueryParams')->andReturn(['name' => 'Hyperf']);
        Context::set(ServerRequestInterface::class, $psrRequest);

        $psrRequest = new Request();
        $this->assertSame(['id' => 1, 'name' => 'Hyperf'], $psrRequest->all());
    }

    public function testRequestInputs()
    {
        $psrRequest = Mockery::mock(ServerRequestInterface::class);
        $psrRequest->shouldReceive('getParsedBody')->andReturn(['id' => 1]);
        $psrRequest->shouldReceive('getQueryParams')->andReturn([]);
        Context::set(ServerRequestInterface::class, $psrRequest);

        $psrRequest = new Request();
        $this->assertSame(['id' => 1, 'name' => 'Hyperf'], $psrRequest->inputs(['id', 'name'], ['name' => 'Hyperf']));
    }

    public function testClearStoredParsedData()
    {
        $psrRequest = new \Hyperf\HttpMessage\Server\Request('GET', '/');
        $psrRequest = $psrRequest->withParsedBody(['id' => 1]);
        Context::set(ServerRequestInterface::class, $psrRequest);

        $request = new Request();
        $this->assertSame(['id' => 1], $request->all());

        $psrRequest = $psrRequest->withParsedBody(['id' => 1, 'name' => 'hyperf']);
        Context::set(ServerRequestInterface::class, $psrRequest);
        $this->assertSame(['id' => 1], $request->all());

        $request->clearStoredParsedData();
        $this->assertSame(['id' => 1, 'name' => 'hyperf'], $request->all());
    }
}
