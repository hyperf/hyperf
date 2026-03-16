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

namespace HyperfTest\WebSocketClient;

use Hyperf\WebSocketClient\Frame;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Swoole\WebSocket\Frame as SwFrame;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class FrameTest extends TestCase
{
    public function testFrame()
    {
        $swframe = Mockery::mock(SwFrame::class);
        $swframe->finish = true;
        $swframe->opcode = 1;
        $frame = new Frame($swframe);

        $this->assertSame($frame->data, (string) $frame);
    }
}
