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

class TaskData
{
    /**
     * @var int
     */
    public $taskId;

    /**
     * @var mixed
     */
    public $data;

    public function __construct(int $taskId, $data)
    {
        $this->taskId = $taskId;
        $this->data = $data;
    }
}
