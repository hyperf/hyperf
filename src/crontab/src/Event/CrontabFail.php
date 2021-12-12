<?php

namespace Hyperf\Crontab\Event;

use Throwable;

class CrontabFail
{
    /**
     * @var Throwable
     */
    public $throwable;

    public function __construct(Throwable $throwable)
    {
        $this->throwable = $throwable;
    }


}