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
namespace HyperfTest\WebSocketClient;

use Hyperf\WebSocketClient\Frame;
use Mockery;
use PHPUnit\Framework\TestCase;
use Swoole\WebSocket\Frame as SwFrame;

/**
 * @internal
 * @coversNothing
 */
class FrameTest extends TestCase
{
    public function testFrame()
    {
        $frame = new Frame(Mockery::mock(SwFrame::class));

        $this->assertSame($frame->data, (string) $frame);
    }
}
