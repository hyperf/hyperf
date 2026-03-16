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

namespace HyperfTest\Crontab;

use Hyperf\Crontab\CrontabManager;
use Hyperf\Crontab\Parser;
use Hyperf\Crontab\Scheduler;
use Hyperf\Support\Reflection\ClassInvoker;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class SchedulerTest extends TestCase
{
    public function testGetSchedules()
    {
        $scheduler = new Scheduler(new CrontabManager(new Parser()));

        $invoker = new ClassInvoker($scheduler);

        $this->assertSame([], $invoker->getSchedules());
    }
}
