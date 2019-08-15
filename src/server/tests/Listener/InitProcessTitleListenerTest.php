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

namespace HyperfTest\Server\Listener;

use Hyperf\Framework\Event\AfterWorkerStart;
use Hyperf\Framework\Event\OnManagerStart;
use Hyperf\Framework\Event\OnStart;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Server\Listener\InitProcessTitleListener;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class InitProcessTitleListenerTest extends TestCase
{
    public function testInitProcessTitleListenerListen()
    {
        $listener = new InitProcessTitleListener();

        $this->assertSame([
            OnStart::class,
            OnManagerStart::class,
            AfterWorkerStart::class,
            BeforeProcessHandle::class,
        ], $listener->listen());
    }
}
