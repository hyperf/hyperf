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

    public function __construct($callback, array $arguments = [])
    {
        $this->callback = $callback;
        $this->arguments = $arguments;
    }
}
