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

namespace HyperfTest\Process\Stub;

use Hyperf\Process\AbstractProcess;

class FooProcess extends AbstractProcess
{
    public bool $enableCoroutine = false;

    public int $restartInterval = 0;

    public static $handled = false;

    public function handle(): void
    {
        static::$handled = true;
    }
}
