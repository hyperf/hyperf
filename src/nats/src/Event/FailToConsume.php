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

namespace Hyperf\Nats\Event;

use Hyperf\Nats\AbstractConsumer;
use Throwable;

class FailToConsume extends Consume
{
    public function __construct(AbstractConsumer $consumer, $data, protected Throwable $throwable)
    {
        parent::__construct($consumer, $data);
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
