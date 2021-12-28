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
namespace HyperfTest\HttpMessage\Stream;

use Hyperf\HttpMessage\Stream\ChunkStream;
use Hyperf\HttpMessage\Stream\SwooleStream;
use PHPUnit\Framework\TestCase;

class ChunkStreamTest extends TestCase
{
    /**
     * @var \Hyperf\HttpMessage\Stream\ChunkStream
     */
    protected $stream;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stream = new ChunkStream();
    }

    public function testRead()
    {
        $this->stream->setStreams([
            new SwooleStream("12345"), new SwooleStream("67890"), new SwooleStream("abcde")
        ]);

        self::assertEquals('123', $this->stream->read(3));
        self::assertEquals('456', $this->stream->read(3));
        self::assertEquals('789', $this->stream->read(3));
        self::assertEquals('0ab', $this->stream->read(3));
        self::assertEquals('cde', $this->stream->read(3));
        self::assertTrue($this->stream->eof());
    }
}
