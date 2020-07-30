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

class FailToConsume extends Consume
{
    /**
     * @var \Throwable
     */
    protected $throwable;

    public function __construct(AbstractConsumer $consumer, $data, \Throwable $throwable)
    {
        parent::__construct($consumer, $data);
        $this->throwable = $throwable;
    }

    public function getThrowable(): \Throwable
    {
        return $this->throwable;
    }
}
