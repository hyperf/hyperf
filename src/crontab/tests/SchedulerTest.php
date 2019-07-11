<?php

namespace HyperfTest\Crontab;


use Hyperf\Crontab\CrontabManager;
use Hyperf\Crontab\Parser;
use Hyperf\Crontab\Scheduler;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

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