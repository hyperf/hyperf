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
namespace Hyperf\Crontab\Event;

use Hyperf\Crontab\PipeMessage;
use Throwable;

class FailToHandle
{
    /**
     * @var PipeMessage
     */
    public $pipeMessage;

    /**
     * @var Throwable
     */
    public $throwable;

    public function __construct(PipeMessage $pipeMessage, Throwable $throwable)
    {
        $this->pipeMessage = $pipeMessage;
        $this->throwable = $throwable;
    }
}
