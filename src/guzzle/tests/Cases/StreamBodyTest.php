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

namespace HyperfTest\Guzzle\Cases;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use Hyperf\Engine\Exception\HttpClientException;
use Hyperf\Engine\Http\Client;
use Hyperf\Engine\Http\RawResponse;
use HyperfTest\Guzzle\Stub\StreamBodyHandlerStub;
use HyperfTest\Guzzle\Stub\StreamClientStub;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Throwable;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class StreamBodyTest extends TestCase
{
    protected array $bodies = [];

    protected array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->bodies as $body) {
            try {
                $body->close();
            } catch (Throwable) {
            }
        }
        foreach ($this->files as $file) {
            @unlink($file);
        }
        Mockery::close();
        parent::tearDown();
    }

    public function testCreatesStreamBodySourceFromBeginning()
    {
        [$path, $size] = $this->createFile(1024 * 1024 + 4096);
        $body = $this->createBody($path, 1024);

        $source = (new StreamBodyHandlerStub())->createStreamBodySourceForTest($body);

        $this->assertIsArray($source);
        $this->assertIsResource($source[0]);
        $this->assertSame($size, $source[1]);
        $this->assertSame(0, ftell($source[0]));

        fclose($source[0]);
    }

    public function testStreamsFileAtThreshold()
    {
        [$path, $size] = $this->createFile(1024 * 1024);
        $body = $this->createBody($path);

        $source = (new StreamBodyHandlerStub())->createStreamBodySourceForTest($body);

        $this->assertIsArray($source);
        $this->assertSame($size, $source[1]);

        fclose($source[0]);
    }

    public function testSkipsSmallBody()
    {
        [$path] = $this->createFile(1024);
        $body = $this->createBody($path);

        $this->assertNull((new StreamBodyHandlerStub())->createStreamBodySourceForTest($body));
    }

    public function testSkipsFileBelowThreshold()
    {
        [$path] = $this->createFile(1024 * 1024 - 1);
        $body = $this->createBody($path);

        $this->assertNull((new StreamBodyHandlerStub())->createStreamBodySourceForTest($body));
    }

    public function testSkipsBodyWithoutFileUri()
    {
        $resource = fopen('php://temp', 'w+b');
        fwrite($resource, str_repeat('x', 1024 * 1024 + 1));
        rewind($resource);

        $body = Utils::streamFor($resource);
        $this->bodies[] = $body;

        $this->assertNull((new StreamBodyHandlerStub())->createStreamBodySourceForTest($body));
    }

    public function testSkipsNonSeekableBody()
    {
        $pair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if ($pair === false) {
            $this->markTestSkipped('stream_socket_pair() is not available.');
        }

        [$left, $right] = $pair;
        $body = Utils::streamFor($left);
        $this->bodies[] = $body;

        try {
            $this->assertNull((new StreamBodyHandlerStub())->createStreamBodySourceForTest($body));
        } finally {
            fclose($right);
        }
    }

    public function testSendRequestUsesStreamBodyAndClosesHandle()
    {
        [$path, $size] = $this->createFile(1024 * 1024 + 4096);
        $body = $this->createBody($path);
        $request = new Request('PUT', 'http://127.0.0.1/upload', [], $body);

        $handler = new StreamBodyHandlerStub();
        $response = $handler->sendRequestForTest(new Client('127.0.0.1', 80), $request, '/upload', [
            'Content-Type' => ['application/octet-stream'],
        ]);

        $this->assertSame('streamed', $response->body);
        $this->assertSame($size, $handler->streamRequest['length']);
        $this->assertSame(0, $handler->streamRequest['position']);
        $this->assertFalse(is_resource($handler->streamRequest['resource']));
    }

    public function testSendRequestFallsBackToStringBody()
    {
        [$path] = $this->createFile(1024);
        $body = $this->createBody($path);
        $request = new Request('PUT', 'http://127.0.0.1/upload', [], $body);

        $client = Mockery::mock(Client::class . '[request]', ['127.0.0.1', 80, false]);
        $client->shouldReceive('request')
            ->once()
            ->with('PUT', '/upload', [], str_repeat("\0", 1024))
            ->andReturn(new RawResponse(200, [], 'ok', '1.1'));

        $handler = new StreamBodyHandlerStub();
        $response = $handler->sendRequestForTest($client, $request, '/upload', []);

        $this->assertSame('ok', $response->body);
        $this->assertNull($handler->streamRequest);
    }

    public function testSendRequestFallsBackWhenStreamingIsNotSupported()
    {
        [$path, $size] = $this->createFile(1024 * 1024 + 4096);
        $body = $this->createBody($path);
        $request = new Request('PUT', 'http://127.0.0.1/upload', [], $body);

        $client = Mockery::mock(Client::class . '[request]', ['127.0.0.1', 80, false]);
        $client->shouldReceive('request')
            ->once()
            ->with('PUT', '/upload', [], str_repeat("\0", $size))
            ->andReturn(new RawResponse(200, [], 'ok', '1.1'));

        $handler = new StreamBodyHandlerStub();
        $handler->streamBodySupported = false;
        $response = $handler->sendRequestForTest($client, $request, '/upload', []);

        $this->assertSame('ok', $response->body);
        $this->assertNull($handler->streamRequest);
    }

    public function testRequestWithStreamBody()
    {
        [$path, $size] = $this->createFile(1024 * 1024 + 4096);
        $body = $this->createBody($path);
        $request = new Request('PUT', 'http://127.0.0.1/upload', [], $body);
        $resource = fopen($path, 'rb');

        $client = new StreamClientStub();
        $handler = new StreamBodyHandlerStub();
        $response = $handler->requestWithStreamBodyForTest($client, $request, '/upload', [
            'X-Test' => ['one', 'two'],
        ], $resource, $size);

        $this->assertSame('done', $response->body);
        $this->assertSame(201, $response->statusCode);
        $this->assertSame(
            ['x-test' => ['a', 'b'], 'content-type' => ['text/plain'], 'set-cookie' => ['sid=1']],
            $response->headers
        );
        $this->assertSame('PUT', $client->calls['method']);
        $this->assertSame([$resource, $size], $client->calls['data']);
        $this->assertSame(['X-Test' => 'one,two'], $client->calls['headers']);
        $this->assertSame('/upload', $client->calls['path']);

        fclose($resource);
    }

    public function testRequestWithStreamBodyThrowsOnSendError()
    {
        [$path, $size] = $this->createFile(1024 * 1024 + 4096);
        $body = $this->createBody($path);
        $request = new Request('PUT', 'http://127.0.0.1/upload', [], $body);
        $resource = fopen($path, 'rb');

        $client = new StreamClientStub();
        $client->errorCode = 5;
        $client->errorMessage = 'send failed';

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('send failed');
        $this->expectExceptionCode(5);

        try {
            (new StreamBodyHandlerStub())->requestWithStreamBodyForTest($client, $request, '/upload', [], $resource, $size);
        } finally {
            fclose($resource);
        }
    }

    protected function createBody(string $path, int $offset = 0): StreamInterface
    {
        $resource = fopen($path, 'rb');
        if ($offset > 0) {
            fseek($resource, $offset);
        }

        $body = Utils::streamFor($resource);
        $this->bodies[] = $body;

        return $body;
    }

    protected function createFile(int $size): array
    {
        $dir = BASE_PATH . '/runtime/guzzle/';
        @mkdir($dir, 0755, true);

        $path = $dir . uniqid('stream_body_', true);
        $resource = fopen($path, 'w+b');
        ftruncate($resource, $size);
        fclose($resource);

        $this->files[] = $path;

        return [$path, $size];
    }
}
