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

use Hyperf\HttpMessage\Stream\ChunkStream;
use Hyperf\HttpMessage\Stream\SwooleFileStream;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Response;
use Hyperf\HttpServer\ResponseEmitter;
use HyperfTest\HttpServer\Stub\SwooleResponseStub;
use Mockery;
use PHPUnit\Framework\TestCase;

class ResponseEmitterTest extends TestCase
{
    /**
     * @var \Hyperf\HttpServer\Response
     */
    protected $psrResponse;

    /**
     * @var \Hyperf\HttpServer\ResponseEmitter
     */
    protected $emitter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->psrResponse = new Response(new \Hyperf\HttpMessage\Base\Response());
        $this->emitter = new ResponseEmitter();
    }

    public function testEmitChunkResponse()
    {
        $swooleResponse = new SwooleResponseStub();
        $psrResponse = $this->psrResponse
            ->withAddedHeader('name', 'hyperf')
            ->withBody(new ChunkStream([
                new SwooleStream("123"), new SwooleStream("456")
            ]));

        $this->emitter->emit($psrResponse, $swooleResponse);

        self::assertEquals(['name' => 'hyperf'], $swooleResponse->headers);
        self::assertEquals("123456\r\n", $swooleResponse->contents);
    }

    public function testEmitFile()
    {
        $file = Mockery::mock(\SplFileInfo::class);
        $file->shouldReceive('isReadable')->andReturn(true);
        $file->shouldReceive('getSize')->andReturn(1);
        $file->shouldReceive('getPathname')->andReturn('hyperf');

        $swooleResponse = new SwooleResponseStub();
        $psrResponse = $this->psrResponse
            ->withAddedHeader('name', 'hyperf')
            ->withBody(new SwooleFileStream($file));

        $this->emitter->emit($psrResponse, $swooleResponse);

        self::assertEquals(['name' => 'hyperf'], $swooleResponse->headers);
        self::assertEquals("hyperf", $swooleResponse->filename);
    }

    public function testEmitNormal()
    {
        $swooleResponse = new SwooleResponseStub();
        $psrResponse = $this->psrResponse
            ->withAddedHeader('name', 'hyperf')
            ->withBody(new SwooleStream("abcd"));

        $this->emitter->emit($psrResponse, $swooleResponse);

        self::assertEquals(['name' => 'hyperf'], $swooleResponse->headers);
        self::assertEquals("abcd\r\n", $swooleResponse->contents);
    }
}
