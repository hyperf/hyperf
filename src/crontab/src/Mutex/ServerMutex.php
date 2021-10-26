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
namespace Hyperf\Crontab\Mutex;

use Hyperf\Crontab\Crontab;

interface ServerMutex
{
    /**
     * Attempt to obtain a server mutex for the given crontab.
     */
    public function attempt(Crontab $crontab): bool;

    /**
     * Get the server mutex for the given crontab.
     */
    public function get(Crontab $crontab): string;
}
