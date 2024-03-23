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

namespace Hyperf\Nsq\Event;

use Hyperf\Nsq\AbstractConsumer;

abstract class Event
{
    public function __construct(protected AbstractConsumer $consumer)
    {
    }

    public function getConsumer(): AbstractConsumer
    {
        return $this->consumer;
    }
}
