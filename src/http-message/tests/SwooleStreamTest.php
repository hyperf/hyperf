<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\HttpMessage;

use Hyperf\HttpMessage\Server\Response;
use Hyperf\HttpMessage\Stream\SwooleFileStream;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Mockery;
use PHPUnit\Framework\TestCase;
use Swoole\Http\Response as SwooleResponse;

/**
 * @internal
 * @coversNothing
 */
class SwooleStreamTest extends TestCase
{
    public function testSwooleFileStream()
    {
        $swooleResponse = Mockery::mock(SwooleResponse::class);
        $file = __FILE__;
        $swooleResponse->shouldReceive('sendfile')->with($file)->once()->andReturn(null);
        $swooleResponse->shouldReceive('status')->with(Mockery::any())->once()->andReturn(200);

        $response = new Response($swooleResponse);
        $response = $response->withBody(new SwooleFileStream($file));

        $this->assertSame(null, $response->send());
    }

    public function testSwooleStream()
    {
        $swooleResponse = Mockery::mock(SwooleResponse::class);
        $content = '{"id":1}';
        $swooleResponse->shouldReceive('end')->with($content)->once()->andReturn(null);
        $swooleResponse->shouldReceive('status')->with(Mockery::any())->once()->andReturn(200);
        $swooleResponse->shouldReceive('header')->with('TOKEN', 'xxx')->once()->andReturn(null);

        $response = new Response($swooleResponse);
        $response = $response->withBody(new SwooleStream($content))->withHeader('TOKEN', 'xxx');

        $this->assertSame(null, $response->send());
    }
}