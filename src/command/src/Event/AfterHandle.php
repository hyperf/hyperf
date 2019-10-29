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

class AfterHandle
{
    /**
     * @var Command
     */
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }
}
