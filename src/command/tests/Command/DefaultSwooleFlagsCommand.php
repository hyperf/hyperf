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

namespace HyperfTest\Command\Command;

use Hyperf\Command\Command;

class DefaultSwooleFlagsCommand extends Command
{
    public function handle()
    {
    }

    public function getHookFlags(): int
    {
        return $this->hookFlags;
    }
}
