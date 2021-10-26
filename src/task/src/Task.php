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
     * @var array|callable
     */
    public $callback;

    /**
     * @var array
     */
    public $arguments;

    /**
     * @var int
     */
    public $workerId;

    public function __construct($callback, array $arguments = [], int $workerId = -1)
    {
        $this->callback = $callback;
        $this->arguments = $arguments;
        $this->workerId = $workerId;
    }
}
