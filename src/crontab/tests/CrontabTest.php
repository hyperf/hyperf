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
        $crontab = clone ((new Crontab())->setName('test')->setRule('* * * * *')->setMemo('test')->setSingleton(true)->setMutexPool('default')->setOnOneServer(true)->setEnable(false));

        $serialized = 'O:22:"Hyperf\Crontab\Crontab":11:{s:4:"name";s:4:"test";s:4:"type";s:8:"callback";s:4:"rule";s:9:"* * * * *";s:9:"singleton";b:1;s:9:"mutexPool";s:7:"default";s:12:"mutexExpires";i:3600;s:11:"onOneServer";b:1;s:8:"callback";N;s:4:"memo";s:4:"test";s:11:"executeTime";N;s:6:"enable";b:0;}';
        $this->assertEquals($serialized, serialize($crontab));

        $unserializeCrontab = unserialize($serialized);

        $this->assertEquals($crontab, $unserializeCrontab);
    }
}
