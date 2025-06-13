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

abstract class Consume extends Event
{
    public function __construct(AbstractConsumer $consumer, protected mixed $data)
    {
        parent::__construct($consumer);
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}
