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
namespace Hyperf\HttpMessage\Factory;

use Hyperf\HttpMessage\Stream\StandardStream;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

use function fopen;

class StreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        return new SwooleStream($content);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return StandardStream::create(fopen($filename, $mode));
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        return StandardStream::create($resource);
    }
}
