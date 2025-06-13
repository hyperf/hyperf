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

abstract class Event
{
    public function __construct(protected Command $command)
    {
    }

    public function getCommand(): Command
    {
        return $this->command;
    }
}
