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
use Hyperf\Crontab\CrontabManager;
use Hyperf\Crontab\Parser;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function Hyperf\Tappable\tap;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class CrontabManagerTest extends TestCase
{
    public function testCrontabRegister()
    {
        $crontab = new Crontab();
        $manager = new CrontabManager(new Parser());

        $manager->register($crontab);
        $this->assertSame([], $manager->getCrontabs());

        $manager->register(tap(new Crontab(), function (Crontab $crontab) {
            $crontab->setName('test')->setRule('* * * * *')->setCallback(static function () {
            });
        }));

        $this->assertArrayHasKey('test', $manager->getCrontabs());

        $manager->register(tap(new Crontab(), function (Crontab $crontab) {
            $crontab->setName('test2')->setRule('* * * * *')->setEnable(false)->setCallback(static function () {
            });
        }));

        $this->assertArrayNotHasKey('test2', $manager->getCrontabs());
    }

    public function testParseUsesCurrentMinuteAsStartTime(): void
    {
        /** @var Parser&MockObject $parser */
        $parser = $this->createMock(Parser::class);
        $parser->expects($this->once())
            ->method('isValid')
            ->willReturn(true);
        $parser->expects($this->once())
            ->method('parse')
            ->with(
                '*/5 * * * * *',
                $this->callback(static fn (int $timestamp): bool => $timestamp % 60 === 0),
                null
            )
            ->willReturn([]);

        $manager = new CrontabManager($parser);
        $manager->register(tap(new Crontab(), static function (Crontab $crontab): void {
            $crontab->setName('seconds')->setRule('*/5 * * * * *')->setCallback(static function (): void {
            });
        }));

        $this->assertSame([], $manager->parse());
    }
}
