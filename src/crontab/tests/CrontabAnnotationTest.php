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
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CrontabAnnotationTest extends TestCase
{
    public function testCallableNotExist()
    {
        $this->expectException(\InvalidArgumentException::class);
        $annotation = new Crontab();
        $annotation->collectClass(FooCron::class);
    }

    public function testIsEnable()
    {
        $annotation = new Crontab();
        $annotation->enableMethod = 'isEnable';
        $annotation->collectClass(FooCron::class);
        $this->assertEquals([FooCron::class, 'execute'], $annotation->callback);
    }
}
