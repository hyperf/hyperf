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

use Hyperf\Crontab\Crontab;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class CrontabTest extends TestCase
{
    public function testCrontab()
    {
        $crontab = clone (new Crontab())->setName('test')->setRule('* * * * *')->setMemo('test')->setSingleton(true)->setMutexPool('default')->setOnOneServer(true)->setEnable(false);

        // file_put_contents(__DIR__ . '/Stub/test.cron', serialize($crontab));
        $serialized = file_get_contents(__DIR__ . '/Stub/test.cron');

        $this->assertEquals($serialized, serialize($crontab));

        $unserializeCrontab = unserialize($serialized);

        $this->assertEquals($crontab, $unserializeCrontab);
    }
}
