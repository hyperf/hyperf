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

namespace Hyperf\AsyncQueue\Event;

use Hyperf\AsyncQueue\MessageInterface;
use Throwable;

class FailedHandle extends Event
{
    public function __construct(MessageInterface $message, protected Throwable $throwable)
    {
        parent::__construct($message);
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
