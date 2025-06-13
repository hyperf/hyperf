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

namespace Hyperf\Crontab\Event;

use Hyperf\Crontab\Crontab;
use Throwable;

class FailToExecute extends Event
{
    public function __construct(Crontab $crontab, public Throwable $throwable)
    {
        parent::__construct($crontab);
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
