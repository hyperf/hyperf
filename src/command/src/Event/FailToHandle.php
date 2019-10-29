<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Command\Event;

use Hyperf\Command\Command;
use Throwable;

class FailToHandle
{
    /**
     * @var Command
     */
    protected $command;

    /**
     * @var Throwable
     */
    protected $throwable;

    public function __construct(Command $command, Throwable $throwable)
    {
        $this->command = $command;
        $this->throwable = $throwable;
    }
}
