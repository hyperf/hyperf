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
namespace HyperfTest\Crontab;

use Hyperf\Crontab\CrontabManager;
use Hyperf\Crontab\Parser;
use Hyperf\Crontab\Scheduler;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * @internal
 * @coversNothing
 */
class SchedulerTest extends TestCase
{
    public function testGetSchedules()
    {
        $scheduler = new Scheduler(new CrontabManager(new Parser()));
        $reflectionMethod = new ReflectionMethod(Scheduler::class, 'getSchedules');
        $reflectionMethod->setAccessible(true);
        $result = $reflectionMethod->invoke($scheduler);
        $this->assertSame([], $result);
    }
}
