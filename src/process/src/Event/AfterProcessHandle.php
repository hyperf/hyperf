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

namespace Hyperf\Process\Event;

use Hyperf\Process\AbstractProcess;

class AfterProcessHandle
{
    public function __construct(public AbstractProcess $process, public int $index)
    {
    }
}
