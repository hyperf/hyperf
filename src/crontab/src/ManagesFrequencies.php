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

namespace Hyperf\Crontab;

use Carbon\Carbon;
use Closure;
use DateTimeZone;

trait ManagesFrequencies
{
    /**
     * The Cron expression representing the event's frequency.
     *
     * @return $this
     */
    public function cron(string $expression): static
    {
        $this->setRule($expression);

        return $this;
    }

    /**
     * Schedule the event to run every second.
     *
     * @return $this
     */
    public function everySecond(): static
    {
        $this->setCrontabInSeconds();

        return $this->spliceIntoPosition(1, '*');
    }

    /**
     * Schedule the event to run every two seconds.
     *
     * @return $this
     */
    public function everyTwoSeconds(): static
    {
        $this->setCrontabInSeconds();

        return $this->spliceIntoPosition(1, '*/2');
    }

    /**
     * Schedule the event to run every five seconds.
     *
     * @return $this
     */
    public function everyFiveSeconds(): static
    {
        $this->setCrontabInSeconds();

        return $this->spliceIntoPosition(1, '*/5');
    }

    /**
     * Schedule the event to run every ten seconds.
     *
     * @return $this
     */
    public function everyTenSeconds(): static
    {
        $this->setCrontabInSeconds();

        return $this->spliceIntoPosition(1, '*/10');
    }

    /**
     * Schedule the event to run every fifteen seconds.
     *
     * @return $this
     */
    public function everyFifteenSeconds(): static
    {
        $this->setCrontabInSeconds();

        return $this->spliceIntoPosition(1, '*/15');
    }

    /**
     * Schedule the event to run every twenty seconds.
     *
     * @return $this
     */
    public function everyTwentySeconds(): static
    {
        $this->setCrontabInSeconds();

        return $this->spliceIntoPosition(1, '*/20');
    }

    /**
     * Schedule the event to run every thirty seconds.
     *
     * @return $this
     */
    public function everyThirtySeconds(): static
    {
        $this->setCrontabInSeconds();

        return $this->spliceIntoPosition(1, '*/30');
    }

    /**
     * Schedule the event to run every minute.
     *
     * @return $this
     */
    public function everyMinute(): static
    {
        return $this->spliceIntoPosition(1, '*');
    }

    /**
     * Schedule the event to run every two minutes.
     *
     * @return $this
     */
    public function everyTwoMinutes(): static
    {
        return $this->spliceIntoPosition(1, '*/2');
    }

    /**
     * Schedule the event to run every three minutes.
     *
     * @return $this
     */
    public function everyThreeMinutes(): static
    {
        return $this->spliceIntoPosition(1, '*/3');
    }

    /**
     * Schedule the event to run every four minutes.
     *
     * @return $this
     */
    public function everyFourMinutes(): static
    {
        return $this->spliceIntoPosition(1, '*/4');
    }

    /**
     * Schedule the event to run every five minutes.
     *
     * @return $this
     */
    public function everyFiveMinutes(): static
    {
        return $this->spliceIntoPosition(1, '*/5');
    }

    /**
     * Schedule the event to run every ten minutes.
     *
     * @return $this
     */
    public function everyTenMinutes(): static
    {
        return $this->spliceIntoPosition(1, '*/10');
    }

    /**
     * Schedule the event to run every fifteen minutes.
     *
     * @return $this
     */
    public function everyFifteenMinutes(): static
    {
        return $this->spliceIntoPosition(1, '*/15');
    }

    /**
     * Schedule the event to run every thirty minutes.
     *
     * @return $this
     */
    public function everyThirtyMinutes(): static
    {
        return $this->spliceIntoPosition(1, '0,30');
    }

    /**
     * Schedule the event to run hourly.
     *
     * @return $this
     */
    public function hourly(): static
    {
        return $this->spliceIntoPosition(1, 0);
    }

    /**
     * Schedule the event to run hourly at a given offset in the hour.
     *
     * @param array|int $offset
     * @return $this
     */
    public function hourlyAt($offset): static
    {
        return $this->hourBasedSchedule($offset, '*');
    }

    /**
     * Schedule the event to run every two hours.
     *
     * @param array|int|string $offset
     * @return $this
     */
    public function everyOddHour($offset = 0): static
    {
        return $this->hourBasedSchedule($offset, '1-23/2');
    }

    /**
     * Schedule the event to run every two hours.
     *
     * @param array|int|string $offset
     * @return $this
     */
    public function everyTwoHours($offset = 0): static
    {
        return $this->hourBasedSchedule($offset, '*/2');
    }

    /**
     * Schedule the event to run every three hours.
     *
     * @param array|int|string $offset
     * @return $this
     */
    public function everyThreeHours($offset = 0): static
    {
        return $this->hourBasedSchedule($offset, '*/3');
    }

    /**
     * Schedule the event to run every four hours.
     *
     * @param array|int|string $offset
     * @return $this
     */
    public function everyFourHours($offset = 0): static
    {
        return $this->hourBasedSchedule($offset, '*/4');
    }

    /**
     * Schedule the event to run every six hours.
     *
     * @param array|int|string $offset
     * @return $this
     */
    public function everySixHours($offset = 0): static
    {
        return $this->hourBasedSchedule($offset, '*/6');
    }

    /**
     * Schedule the event to run daily.
     *
     * @return $this
     */
    public function daily(): static
    {
        return $this->hourBasedSchedule(0, 0);
    }

    /**
     * Schedule the command at a given time.
     *
     * @return $this
     */
    public function at(string $time): static
    {
        return $this->dailyAt($time);
    }

    /**
     * Schedule the event to run daily at a given time (10:00, 19:30, etc).
     *
     * @return $this
     */
    public function dailyAt(string $time): static
    {
        $segments = explode(':', $time);

        return $this->spliceIntoPosition(2, (int) $segments[0])
            ->spliceIntoPosition(1, count($segments) === 2 ? (int) $segments[1] : '0');
    }

    /**
     * Schedule the event to run twice daily.
     *
     * @return $this
     */
    public function twiceDaily(int $first = 1, int $second = 13): static
    {
        return $this->twiceDailyAt($first, $second, 0);
    }

    /**
     * Schedule the event to run twice daily at a given offset.
     *
     * @return $this
     */
    public function twiceDailyAt(int $first = 1, int $second = 13, int $offset = 0): static
    {
        $hours = $first . ',' . $second;

        return $this->hourBasedSchedule($offset, $hours);
    }

    /**
     * Schedule the event to run only on weekdays.
     *
     * @return $this
     */
    public function weekdays(): static
    {
        return $this->days(Scheduler::MONDAY . '-' . Scheduler::FRIDAY);
    }

    /**
     * Schedule the event to run only on weekends.
     *
     * @return $this
     */
    public function weekends(): static
    {
        return $this->days(Scheduler::SATURDAY . ',' . Scheduler::SUNDAY);
    }

    /**
     * Schedule the event to run only on Mondays.
     *
     * @return $this
     */
    public function mondays(): static
    {
        return $this->days(Scheduler::MONDAY);
    }

    /**
     * Schedule the event to run only on Tuesdays.
     *
     * @return $this
     */
    public function tuesdays(): static
    {
        return $this->days(Scheduler::TUESDAY);
    }

    /**
     * Schedule the event to run only on Wednesdays.
     *
     * @return $this
     */
    public function wednesdays(): static
    {
        return $this->days(Scheduler::WEDNESDAY);
    }

    /**
     * Schedule the event to run only on Thursdays.
     *
     * @return $this
     */
    public function thursdays(): static
    {
        return $this->days(Scheduler::THURSDAY);
    }

    /**
     * Schedule the event to run only on Fridays.
     *
     * @return $this
     */
    public function fridays(): static
    {
        return $this->days(Scheduler::FRIDAY);
    }

    /**
     * Schedule the event to run only on Saturdays.
     *
     * @return $this
     */
    public function saturdays(): static
    {
        return $this->days(Scheduler::SATURDAY);
    }

    /**
     * Schedule the event to run only on Sundays.
     *
     * @return $this
     */
    public function sundays(): static
    {
        return $this->days(Scheduler::SUNDAY);
    }

    /**
     * Schedule the event to run weekly.
     *
     * @return $this
     */
    public function weekly(): static
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(5, 0);
    }

    /**
     * Schedule the event to run weekly on a given day and time.
     *
     * @param array|mixed $dayOfWeek
     * @return $this
     */
    public function weeklyOn($dayOfWeek, string $time = '0:0'): static
    {
        $this->dailyAt($time);

        return $this->days($dayOfWeek);
    }

    /**
     * Schedule the event to run monthly.
     *
     * @return $this
     */
    public function monthly(): static
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1);
    }

    /**
     * Schedule the event to run monthly on a given day and time.
     *
     * @return $this
     */
    public function monthlyOn(int $dayOfMonth = 1, string $time = '0:0'): static
    {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(3, $dayOfMonth);
    }

    /**
     * Schedule the event to run twice monthly at a given time.
     *
     * @return $this
     */
    public function twiceMonthly(int $first = 1, int $second = 16, string $time = '0:0'): static
    {
        $daysOfMonth = $first . ',' . $second;

        $this->dailyAt($time);

        return $this->spliceIntoPosition(3, $daysOfMonth);
    }

    /**
     * Schedule the event to run on the last day of the month.
     *
     * @return $this
     */
    public function lastDayOfMonth(string $time = '0:0'): static
    {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(3, Carbon::now()->endOfMonth()->day);
    }

    /**
     * Schedule the event to run quarterly.
     *
     * @return $this
     */
    public function quarterly(): static
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1)
            ->spliceIntoPosition(4, '1-12/3');
    }

    /**
     * Schedule the event to run yearly.
     *
     * @return $this
     */
    public function yearly(): static
    {
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, 1)
            ->spliceIntoPosition(4, 1);
    }

    /**
     * Schedule the event to run yearly on a given month, day, and time.
     *
     * @return $this
     */
    public function yearlyOn(int $month = 1, int|string $dayOfMonth = 1, string $time = '0:0'): static
    {
        $this->dailyAt($time);

        return $this->spliceIntoPosition(3, $dayOfMonth)
            ->spliceIntoPosition(4, $month);
    }

    /**
     * Set the days of the week the command should run on.
     *
     * @param array|mixed $days
     * @return $this
     */
    public function days($days): static
    {
        $days = is_array($days) ? $days : func_get_args();

        return $this->spliceIntoPosition(5, implode(',', $days));
    }

    /**
     * Set the timezone the date should be evaluated on.
     *
     * @param DateTimeZone|string $timezone
     * @return $this
     */
    public function timezone($timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Set the cron rule to second level.
     *
     * @return $this
     */
    protected function setCrontabInSeconds(): static
    {
        $this->setRule('* * * * * *');

        return $this;
    }

    /**
     * Schedule the event to run at the given minutes and hours.
     *
     * @param array|int|string $minutes
     * @param array|int|string $hours
     * @return $this
     */
    protected function hourBasedSchedule($minutes, $hours): static
    {
        $minutes = is_array($minutes) ? implode(',', $minutes) : $minutes;

        $hours = is_array($hours) ? implode(',', $hours) : $hours;

        return $this->spliceIntoPosition(1, $minutes)
            ->spliceIntoPosition(2, $hours);
    }

    /**
     * Splice the given value into the given position of the expression.
     *
     * @return $this
     */
    protected function spliceIntoPosition(int $position, int|string $value): static
    {
        $segments = preg_split('/\s+/', $this->rule ?: '* * * * *');

        $segments[$position - 1] = $value;

        return $this->cron(implode(' ', $segments));
    }

    /**
     * Schedule the event to run between start and end time.
     */
    private function inTimeInterval(string $startTime, string $endTime): Closure
    {
        [$now, $startTime, $endTime] = [
            Carbon::now($this->timezone),
            Carbon::parse($startTime, $this->timezone),
            Carbon::parse($endTime, $this->timezone),
        ];

        if ($endTime->lessThan($startTime)) {
            if ($startTime->greaterThan($now)) {
                $startTime->subDay();
            } else {
                $endTime->addDay();
            }
        }

        return function () use ($now, $startTime, $endTime) {
            return $now->between($startTime, $endTime);
        };
    }
}
