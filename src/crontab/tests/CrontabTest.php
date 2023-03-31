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
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CrontabTest extends TestCase
{
    public function testCrontab()
    {
        $crontab = clone (new Crontab())->setName('test')->setRule('* * * * *')->setMemo('test')->setSingleton(true)->setMutexPool('default')->setOnOneServer(true)->setEnable(false);

        $serialized = "O:22:\"Hyperf\\Crontab\\Crontab\":11:{s:7:\"\x00*\x00name\";s:4:\"test\";s:7:\"\x00*\x00type\";s:8:\"callback\";s:7:\"\x00*\x00rule\";s:9:\"* * * * *\";s:12:\"\x00*\x00singleton\";b:1;s:12:\"\x00*\x00mutexPool\";s:7:\"default\";s:15:\"\x00*\x00mutexExpires\";i:3600;s:14:\"\x00*\x00onOneServer\";b:1;s:11:\"\x00*\x00callback\";N;s:7:\"\x00*\x00memo\";s:4:\"test\";s:14:\"\x00*\x00executeTime\";N;s:9:\"\x00*\x00enable\";b:0;}";
        $this->assertEquals($serialized, serialize($crontab));

        $unserializeCrontab = unserialize($serialized);

        $this->assertEquals($crontab, $unserializeCrontab);
    }
}
