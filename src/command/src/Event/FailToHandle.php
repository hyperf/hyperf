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

namespace Hyperf\Command\Event;

use Hyperf\Command\Command;
use Throwable;

class FailToHandle extends Event
{
    public function __construct(Command $command, protected Throwable $throwable)
    {
        parent::__construct($command);
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
