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

use Carbon\Carbon;
use Hyperf\Crontab\Crontab;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class FrequencyTest extends TestCase
{
    /*
     * @var \Hyperf\Crontab\Crontab
     */
    protected $crontab;

    protected function setUp(): void
    {
        $this->crontab = new Crontab();
    }

    public function testEveryMinute()
    {
        $this->assertSame('* * * * *', $this->crontab->everyMinute()->getRule());
    }

    public function testEveryXMinutes()
    {
        $this->assertSame('*/2 * * * *', $this->crontab->everyTwoMinutes()->getRule());
        $this->assertSame('*/3 * * * *', $this->crontab->everyThreeMinutes()->getRule());
        $this->assertSame('*/4 * * * *', $this->crontab->everyFourMinutes()->getRule());
        $this->assertSame('*/5 * * * *', $this->crontab->everyFiveMinutes()->getRule());
        $this->assertSame('*/10 * * * *', $this->crontab->everyTenMinutes()->getRule());
        $this->assertSame('*/15 * * * *', $this->crontab->everyFifteenMinutes()->getRule());
        $this->assertSame('0,30 * * * *', $this->crontab->everyThirtyMinutes()->getRule());
    }

    public function testDaily()
    {
        $this->assertSame('0 0 * * *', $this->crontab->daily()->getRule());
    }

    public function testDailyAt()
    {
        $this->assertSame('8 13 * * *', $this->crontab->dailyAt('13:08')->getRule());
    }

    public function testTwiceDaily()
    {
        $this->assertSame('0 3,15 * * *', $this->crontab->twiceDaily(3, 15)->getRule());
    }

    public function testTwiceDailyAt()
    {
        $this->assertSame('5 3,15 * * *', $this->crontab->twiceDailyAt(3, 15, 5)->getRule());
    }

    public function testWeekly()
    {
        $this->assertSame('0 0 * * 0', $this->crontab->weekly()->getRule());
    }

    public function testWeeklyOn()
    {
        $this->assertSame('0 8 * * 1', $this->crontab->weeklyOn(1, '8:00')->getRule());
    }

    public function testOverrideWithHourly()
    {
        $this->assertSame('0 * * * *', $this->crontab->everyFiveMinutes()->hourly()->getRule());
        $this->assertSame('37 * * * *', $this->crontab->hourlyAt(37)->getRule());
        $this->assertSame('*/10 * * * *', $this->crontab->hourlyAt('*/10')->getRule());
        $this->assertSame('15,30,45 * * * *', $this->crontab->hourlyAt([15, 30, 45])->getRule());
    }

    public function testHourly()
    {
        $this->assertSame('0 */2 * * *', $this->crontab->everyTwoHours()->getRule());
        $this->assertSame('0 */3 * * *', $this->crontab->everyThreeHours()->getRule());
        $this->assertSame('0 */4 * * *', $this->crontab->everyFourHours()->getRule());
        $this->assertSame('0 */6 * * *', $this->crontab->everySixHours()->getRule());

        $this->assertSame('37 1-23/2 * * *', $this->crontab->everyOddHour(37)->getRule());
        $this->assertSame('37 */2 * * *', $this->crontab->everyTwoHours(37)->getRule());
        $this->assertSame('37 */3 * * *', $this->crontab->everyThreeHours(37)->getRule());
        $this->assertSame('37 */4 * * *', $this->crontab->everyFourHours(37)->getRule());
        $this->assertSame('37 */6 * * *', $this->crontab->everySixHours(37)->getRule());

        $this->assertSame('*/10 1-23/2 * * *', $this->crontab->everyOddHour('*/10')->getRule());
        $this->assertSame('*/10 */2 * * *', $this->crontab->everyTwoHours('*/10')->getRule());
        $this->assertSame('*/10 */3 * * *', $this->crontab->everyThreeHours('*/10')->getRule());
        $this->assertSame('*/10 */4 * * *', $this->crontab->everyFourHours('*/10')->getRule());
        $this->assertSame('*/10 */6 * * *', $this->crontab->everySixHours('*/10')->getRule());

        $this->assertSame('15,30,45 1-23/2 * * *', $this->crontab->everyOddHour([15, 30, 45])->getRule());
        $this->assertSame('15,30,45 */2 * * *', $this->crontab->everyTwoHours([15, 30, 45])->getRule());
        $this->assertSame('15,30,45 */3 * * *', $this->crontab->everyThreeHours([15, 30, 45])->getRule());
        $this->assertSame('15,30,45 */4 * * *', $this->crontab->everyFourHours([15, 30, 45])->getRule());
        $this->assertSame('15,30,45 */6 * * *', $this->crontab->everySixHours([15, 30, 45])->getRule());
    }

    public function testMonthly()
    {
        $this->assertSame('0 0 1 * *', $this->crontab->monthly()->getRule());
    }

    public function testMonthlyOn()
    {
        $this->assertSame('0 15 4 * *', $this->crontab->monthlyOn(4, '15:00')->getRule());
    }

    public function testLastDayOfMonth()
    {
        Carbon::setTestNow('2020-10-10 10:10:10');

        $this->assertSame('0 0 31 * *', $this->crontab->lastDayOfMonth()->getRule());

        Carbon::setTestNow(null);
    }

    public function testTwiceMonthly()
    {
        $this->assertSame('0 0 1,16 * *', $this->crontab->twiceMonthly(1, 16)->getRule());
    }

    public function testTwiceMonthlyAtTime()
    {
        $this->assertSame('30 1 1,16 * *', $this->crontab->twiceMonthly(1, 16, '1:30')->getRule());
    }

    public function testMonthlyOnWithMinutes()
    {
        $this->assertSame('15 15 4 * *', $this->crontab->monthlyOn(4, '15:15')->getRule());
    }

    public function testWeekdaysDaily()
    {
        $this->assertSame('0 0 * * 1-5', $this->crontab->weekdays()->daily()->getRule());
    }

    public function testWeekdaysHourly()
    {
        $this->assertSame('0 * * * 1-5', $this->crontab->weekdays()->hourly()->getRule());
    }

    public function testWeekdays()
    {
        $this->assertSame('* * * * 1-5', $this->crontab->weekdays()->getRule());
    }

    public function testWeekends()
    {
        $this->assertSame('* * * * 6,0', $this->crontab->weekends()->getRule());
    }

    public function testSundays()
    {
        $this->assertSame('* * * * 0', $this->crontab->sundays()->getRule());
    }

    public function testMondays()
    {
        $this->assertSame('* * * * 1', $this->crontab->mondays()->getRule());
    }

    public function testTuesdays()
    {
        $this->assertSame('* * * * 2', $this->crontab->tuesdays()->getRule());
    }

    public function testWednesdays()
    {
        $this->assertSame('* * * * 3', $this->crontab->wednesdays()->getRule());
    }

    public function testThursdays()
    {
        $this->assertSame('* * * * 4', $this->crontab->thursdays()->getRule());
    }

    public function testFridays()
    {
        $this->assertSame('* * * * 5', $this->crontab->fridays()->getRule());
    }

    public function testSaturdays()
    {
        $this->assertSame('* * * * 6', $this->crontab->saturdays()->getRule());
    }

    public function testQuarterly()
    {
        $this->assertSame('0 0 1 1-12/3 *', $this->crontab->quarterly()->getRule());
    }

    public function testYearly()
    {
        $this->assertSame('0 0 1 1 *', $this->crontab->yearly()->getRule());
    }

    public function testYearlyOn()
    {
        $this->assertSame('8 15 5 4 *', $this->crontab->yearlyOn(4, 5, '15:08')->getRule());
    }

    public function testYearlyOnMondaysOnly()
    {
        $this->assertSame('1 9 * 7 1', $this->crontab->mondays()->yearlyOn(7, '*', '09:01')->getRule());
    }

    public function testYearlyOnTuesdaysAndDayOfMonth20()
    {
        $this->assertSame('1 9 20 7 2', $this->crontab->tuesdays()->yearlyOn(7, 20, '09:01')->getRule());
    }
}
