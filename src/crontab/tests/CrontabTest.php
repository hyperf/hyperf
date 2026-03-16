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
        $crontab = clone (new Crontab())
            ->setName('test')
            ->setRule('* * * * *')
            ->setMemo('test')
            ->setSingleton(true)
            ->setMutexPool('default')
            ->setMutexExpires(60)
            ->setOnOneServer(true)
            ->setEnable(false);

        $this->assertEquals('test', $crontab->getName());
        $this->assertEquals('* * * * *', $crontab->getRule());
        $this->assertEquals('test', $crontab->getMemo());
        $this->assertTrue($crontab->isSingleton());
        $this->assertEquals('default', $crontab->getMutexPool());
        $this->assertEquals(60, $crontab->getMutexExpires());
        $this->assertTrue($crontab->isOnOneServer());
        $this->assertFalse($crontab->isEnable());
    }

    public function testSerializeAndUnserialize()
    {
        $crontab = clone (new Crontab())
            ->setName('test')
            ->setRule('* * * * *')
            ->setMemo('test')
            ->setSingleton(true)
            ->setMutexPool('default')
            ->setMutexExpires(60)
            ->setOnOneServer(true)
            ->setEnable(true);

        $serialized = serialize($crontab);

        $this->assertEquals($serialized, serialize($crontab));

        $unserializeCrontab = unserialize($serialized);

        $this->assertEquals($crontab, $unserializeCrontab);
    }
}
