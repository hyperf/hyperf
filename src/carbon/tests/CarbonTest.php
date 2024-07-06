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

namespace HyperfTest\Carbon;

use Carbon\Carbon as BaseCarbon;
use Hyperf\Carbon\Carbon;
use Hyperf\Carbon\Listener\CarbonListener;
use Hyperf\Contract\TranslatorInterface;
use Hyperf\Framework\Event\BootApplication;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 * @coversNothing
 */
class CarbonTest extends TestCase
{
    protected ?string $defaultLocale = null;

    protected function setUp(): void
    {
        $this->defaultLocale = Carbon::getLocale();
    }

    protected function tearDown(): void
    {
        Carbon::setLocale($this->defaultLocale);
        Mockery::close();
    }

    public function testSetLocale()
    {
        $this->assertSame($this->defaultLocale, Carbon::getLocale());
        $this->assertSame($this->defaultLocale, BaseCarbon::getLocale());

        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('has')->with(TranslatorInterface::class)->andReturn(true);
        $container->shouldReceive('get')->with(TranslatorInterface::class)->andReturn($translator = Mockery::mock(TranslatorInterface::class));
        $translator->shouldReceive('getLocale')->andReturn('zh_CN');

        $listener = new CarbonListener($container);
        $listener->process(new BootApplication());

        $this->assertSame('zh_CN', Carbon::getLocale());
        $this->assertSame('zh_CN', BaseCarbon::getLocale());
    }

    public function testCreateFromUid()
    {
        $ulid = Carbon::createFromId('01DXH9C4P0ED4AGJJP9CRKQ55C');
        $this->assertEquals('2020-01-01 19:30:00.000000', $ulid->toDateTimeString('microsecond'));

        $uuidv1 = Carbon::createFromId('71513cb4-f071-11ed-a0cf-325096b39f47');
        $this->assertEquals('2023-05-12 03:02:34.147346', $uuidv1->toDateTimeString('microsecond'));

        $uuidv2 = Carbon::createFromId('000003e8-f072-21ed-9200-325096b39f47');
        $this->assertEquals('2023-05-12 03:06:33.529139', $uuidv2->toDateTimeString('microsecond'));

        $uuidv6 = Carbon::createFromId('1edf0746-5d1c-6ce8-88ad-e0cb4effa035');
        $this->assertEquals('2023-05-12 03:23:43.347428', $uuidv6->toDateTimeString('microsecond'));

        $uuidv7 = Carbon::createFromId('01880dfa-2825-72e4-acbb-b1e4981cf8af');
        $this->assertEquals('2023-05-12 03:21:18.117000', $uuidv7->toDateTimeString('microsecond'));
    }
}
