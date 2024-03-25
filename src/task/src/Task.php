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

namespace Hyperf\Task;

class Task
{
    /**
     * @param array|callable $callback
     */
    public function __construct(public mixed $callback, public array $arguments = [], public int $workerId = -1)
    {
    }
}
