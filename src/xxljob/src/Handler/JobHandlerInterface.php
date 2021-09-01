<?php

declare(strict_types=1);

namespace Hyperf\XxlJob\Handler;

interface JobHandlerInterface
{
    /**
     * The logical of process will place in here.
     */
    public function handle(): void;
}
