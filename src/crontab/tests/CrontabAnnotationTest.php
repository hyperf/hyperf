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

use Hyperf\Crontab\Annotation\Crontab;
use HyperfTest\Crontab\Stub\FooCron;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class CrontabAnnotationTest extends TestCase
{
    public function testCallableNotExist()
    {
        $this->expectException(InvalidArgumentException::class);
        $annotation = new Crontab();
        $annotation->collectClass(FooCron::class);
    }

    public function testCallableNormal()
    {
        $annotation = new Crontab();
        $annotation->callback = 'execute';
        $annotation->collectClass(FooCron::class);
        $this->assertEquals([FooCron::class, 'execute'], $annotation->callback);
    }

    public function testEnable()
    {
        $annotation = new Crontab();
        $annotation->callback = 'execute';
        $annotation->collectClass(FooCron::class);
        $this->assertTrue($annotation->enable);

        $annotation = new Crontab();
        $annotation->callback = 'execute';
        $annotation->enable = 'true';
        $annotation->collectClass(FooCron::class);
        $this->assertTrue($annotation->enable);

        $annotation = new Crontab();
        $annotation->callback = 'execute';
        $annotation->enable = 'isEnable';
        $annotation->collectClass(FooCron::class);
        $this->assertEquals([FooCron::class, 'isEnable'], $annotation->enable);
    }

    public function testCollectMethod()
    {
        $annotation = new Crontab();
        $annotation->collectMethod(FooCron::class, 'cron');
        $this->assertSame(FooCron::class . '::cron', $annotation->name);
        $this->assertSame([FooCron::class, 'cron'], $annotation->callback);
    }
}
