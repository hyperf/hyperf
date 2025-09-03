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
        $psrRequest->shouldReceive('getUploadedFiles')->andReturn([]);
        RequestContext::set($psrRequest);

        $psrRequest = new Request();
        $this->assertSame(['name' => 'Hyperf', 'id' => 1, 123 => '123'], $psrRequest->all());

        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $psrRequest->shouldReceive('getParsedBody')->andReturn(['name' => 'Hyperf']);
        $psrRequest->shouldReceive('getQueryParams')->andReturn(['id' => 1, '123' => '123']);
        $psrRequest->shouldReceive('getUploadedFiles')->andReturn([]);
        RequestContext::set($psrRequest);

        $psrRequest = new Request();

        $this->assertSame(['name' => 'Hyperf', 'id' => 1, 123 => '123'], $psrRequest->all());
    }

    public function testRequestAllByReplace()
    {
        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $psrRequest->shouldReceive('getParsedBody')->andReturn(['id' => 1, 'data' => ['id' => 2]]);
        $psrRequest->shouldReceive('getQueryParams')->andReturn(['data' => 'Hyperf']);
        $psrRequest->shouldReceive('getUploadedFiles')->andReturn([]);
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

    public function testRequestAllWithKeys()
    {
        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $psrRequest->shouldReceive('getParsedBody')->andReturn(['id' => 1, 'name' => 'Hyperf', 'email' => 'test@example.com']);
        $psrRequest->shouldReceive('getQueryParams')->andReturn(['page' => 2, 'limit' => 10]);
        $psrRequest->shouldReceive('getUploadedFiles')->andReturn(['avatar' => new UploadedFile('/tmp/avatar', 100, 0)]);
        RequestContext::set($psrRequest);

        $request = new Request();

        // Test with array of keys
        $result = $request->all(['id', 'name', 'page']);
        $this->assertEquals(['id' => 1, 'name' => 'Hyperf', 'page' => 2], $result);

        // Test with individual arguments
        $result = $request->all('id', 'email');
        $this->assertEquals(['id' => 1, 'email' => 'test@example.com'], $result);

        // Test with non-existent keys
        $result = $request->all(['id', 'nonexistent']);
        $this->assertEquals(['id' => 1, 'nonexistent' => null], $result);
    }

    public function testRequestAllWithFiles()
    {
        $uploadedFile = new UploadedFile('/tmp/test_file', 500, 0);

        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $psrRequest->shouldReceive('getParsedBody')->andReturn(['id' => 1, 'name' => 'Hyperf']);
        $psrRequest->shouldReceive('getQueryParams')->andReturn(['page' => 1]);
        $psrRequest->shouldReceive('getUploadedFiles')->andReturn(['file' => $uploadedFile]);
        RequestContext::set($psrRequest);

        $request = new Request();
        $result = $request->all();

        $this->assertEquals(1, $result['id']);
        $this->assertEquals('Hyperf', $result['name']);
        $this->assertEquals(1, $result['page']);
        $this->assertSame($uploadedFile, $result['file']);
    }

    public function testRequestData()
    {
        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $psrRequest->shouldReceive('getParsedBody')->andReturn(['id' => 1, 'name' => 'Hyperf']);
        $psrRequest->shouldReceive('getQueryParams')->andReturn(['page' => 2]);
        $psrRequest->shouldReceive('getUploadedFiles')->andReturn(['file' => new UploadedFile('/tmp/test', 100, 0)]);
        RequestContext::set($psrRequest);

        $request = new Request();

        // Test data() with specific key
        $this->assertEquals(1, $request->data('id'));
        $this->assertEquals('Hyperf', $request->data('name'));
        $this->assertEquals(2, $request->data('page'));
        $this->assertEquals('default', $request->data('nonexistent', 'default'));

        // Test data() without key (should return all data)
        $allData = $request->data();
        $this->assertEquals(1, $allData['id']);
        $this->assertEquals('Hyperf', $allData['name']);
        $this->assertEquals(2, $allData['page']);
        $this->assertInstanceOf(UploadedFile::class, $allData['file']);
    }

    public function testRequestAllFiles()
    {
        $uploadedFile1 = new UploadedFile('/tmp/file1', 100, 0);
        $uploadedFile2 = new UploadedFile('/tmp/file2', 200, 0);

        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $psrRequest->shouldReceive('getUploadedFiles')->andReturn([
            'file1' => $uploadedFile1,
            'file2' => $uploadedFile2,
        ]);
        RequestContext::set($psrRequest);

        $request = new Request();
        $files = $request->allFiles();

        $this->assertCount(2, $files);
        $this->assertSame($uploadedFile1, $files['file1']);
        $this->assertSame($uploadedFile2, $files['file2']);
    }

    public function testRequestInputWithNullKey()
    {
        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $psrRequest->shouldReceive('getParsedBody')->andReturn(['id' => 1, 'name' => 'Hyperf']);
        $psrRequest->shouldReceive('getQueryParams')->andReturn(['page' => 2, 'limit' => 10]);
        RequestContext::set($psrRequest);

        $request = new Request();

        // Test input() with null key should return all input data
        $result = $request->input(null);
        $this->assertEquals(['page' => 2, 'limit' => 10, 'id' => 1, 'name' => 'Hyperf'], $result);

        // Test with default value - data_get with null key and non-empty data ignores default
        $result2 = $request->input(null, ['default' => 'value']);
        $this->assertEquals(['page' => 2, 'limit' => 10, 'id' => 1, 'name' => 'Hyperf'], $result2);
    }

    public function testRequestInputWithNullKeyEmptyData()
    {
        // Test input() with null key on empty data
        $psrRequest = Mockery::mock(ServerRequestPlusInterface::class);
        $psrRequest->shouldReceive('getParsedBody')->andReturn([]);
        $psrRequest->shouldReceive('getQueryParams')->andReturn([]);
        RequestContext::set($psrRequest);

        $request = new Request();
        $result = $request->input(null);
        $this->assertEquals([], $result); // Empty data returns empty array
    }
}
