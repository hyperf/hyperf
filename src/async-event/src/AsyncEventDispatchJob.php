<?php

declare(strict_types=1);
/**
 * This file is part of MangaToon server projects.
 */
namespace Hyperf\AsyncEvent;

use Hyperf\AsyncQueue\Job;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\ApplicationContext;

class AsyncEventDispatchJob extends Job
{
    public $event;

    protected $maxAttempts = 0;

    public function __construct($event)
    {
        $this->event = $event;
    }

    public function handle()
    {
        ApplicationContext::getContainer()->get(StdoutLoggerInterface::class)->debug('Async Event: dispatch --> '.get_class($this->event));
        ApplicationContext::getContainer()->get(AsyncEventDispatcher::class)->dispatchNow($this->event);
    }
}
