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

class Event
{
    public function __construct(protected MessageInterface $message)
    {
    }

    public function getMessage(): MessageInterface
    {
        return $this->message;
    }
}
