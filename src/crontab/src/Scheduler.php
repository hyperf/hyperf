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

use SplQueue;

class Scheduler
{
    protected SplQueue $schedules;

    public function __construct(protected CrontabManager $crontabManager)
    {
        $this->schedules = new SplQueue();
    }

    public function schedule(): SplQueue
    {
        foreach ($this->getSchedules() as $schedule) {
            $this->schedules->enqueue($schedule);
        }
        return $this->schedules;
    }

    protected function getSchedules(): array
    {
        return $this->crontabManager->parse();
    }
}
