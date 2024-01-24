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
namespace Hyperf\Command;

use Closure;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\Schedule;
use Psr\Container\ContainerInterface;

/**
 * @method Crontab cron(string $expression)
 * @method Crontab everySecond()
 * @method Crontab everyTwoSeconds()
 * @method Crontab everyFiveSeconds()
 * @method Crontab everyTenSeconds()
 * @method Crontab everyFifteenSeconds()
 * @method Crontab everyTwentySeconds()
 * @method Crontab everyThirtySeconds()
 * @method Crontab everyMinute()
 * @method Crontab everyTwoMinutes()
 * @method Crontab everyThreeMinutes()
 * @method Crontab everyFourMinutes()
 * @method Crontab everyFiveMinutes()
 * @method Crontab everyTenMinutes()
 * @method Crontab everyFifteenMinutes()
 * @method Crontab everyThirtyMinutes()
 * @method Crontab hourly()
 * @method Crontab hourlyAt($offset)
 * @method Crontab everyOddHour($offset = 0)
 * @method Crontab everyTwoHours($offset = 0)
 * @method Crontab everyThreeHours($offset = 0)
 * @method Crontab everyFourHours($offset = 0)
 * @method Crontab everySixHours($offset = 0)
 * @method Crontab daily()
 * @method Crontab at(string $time)
 * @method Crontab dailyAt(string $time)
 * @method Crontab twiceDaily(int $first = 1, int $second = 13)
 * @method Crontab twiceDailyAt(int $first = 1, int $second = 13, int $offset = 0)
 * @method Crontab weekdays()
 * @method Crontab weekends()
 * @method Crontab mondays()
 * @method Crontab tuesdays()
 * @method Crontab wednesdays()
 * @method Crontab thursdays()
 * @method Crontab fridays()
 * @method Crontab saturdays()
 * @method Crontab sundays()
 * @method Crontab weekly()
 * @method Crontab weeklyOn($dayOfWeek, string $time = '0:0')
 * @method Crontab monthly()
 * @method Crontab monthlyOn(int $dayOfMonth = 1, string $time = '0:0')
 * @method Crontab twiceMonthly(int $first = 1, int $second = 16, string $time = '0:0')
 * @method Crontab lastDayOfMonth(string $time = '0:0')
 * @method Crontab quarterly()
 * @method Crontab yearly()
 * @method Crontab yearlyOn(int $month = 1, int|string $dayOfMonth = 1, string $time = '0:0')
 * @method Crontab days($days)
 */
final class ClosureCommand extends Command
{
    private ParameterParser $parameterParser;

    private ?Crontab $crontab = null;

    public function __construct(
        private ContainerInterface $container,
        string $signature,
        private Closure $closure
    ) {
        $this->signature = $signature;
        $this->parameterParser = $container->get(ParameterParser::class);

        parent::__construct();
    }

    public function __call($name, $arguments)
    {
        $this->crontab ??= Schedule::command($this->getName())
            ->setName($this->getName())
            ->setMemo($this->getDescription());

        return $this->crontab->{$name}(...$arguments);
    }

    public function handle()
    {
        $inputs = array_merge($this->input->getArguments(), $this->input->getOptions());
        $parameters = $this->parameterParser->parseClosureParameters($this->closure, $inputs);

        return $this->closure->call($this, ...$parameters);
    }

    public function describe(string $description): self
    {
        $this->setDescription($description);

        return $this;
    }
}
