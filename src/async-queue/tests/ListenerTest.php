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

namespace HyperfTest\AsyncQueue;

use Hyperf\AsyncQueue\Driver\DriverInterface;
use Hyperf\AsyncQueue\Event\QueueLength;
use Hyperf\AsyncQueue\Listener\ReloadChannelListener;
use Hyperf\Contract\StdoutLoggerInterface;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testReloadChannelListener()
    {
        $logger = Mockery::mock(StdoutLoggerInterface::class);
        $logger->shouldReceive('info')->withAnyArgs()->once()->andReturnUsing(function ($message) {
            $this->assertSame('timeout channel reload 10 messages to waiting channel success.', $message);
        });
        $driver = Mockery::mock(DriverInterface::class);
        $driver->shouldReceive('reload')->withAnyArgs()->andReturn(0);
        $listener = new ReloadChannelListener($logger);
        $listener->process(new QueueLength($driver, 'failed', 10));
        $listener->process(new QueueLength($driver, 'timeout', 10));
        $listener->process(new QueueLength($driver, 'timeout', 0));
    }
}
