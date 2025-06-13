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

class SwooleFlagsCommand extends Command
{
    protected int $hookFlags = SWOOLE_HOOK_CURL | SWOOLE_HOOK_ALL;

    public function handle()
    {
    }

    public function getHookFlags(): int
    {
        return $this->hookFlags;
    }
}
