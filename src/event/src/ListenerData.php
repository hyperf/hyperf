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
namespace Hyperf\Event;

class ListenerData
{
    /**
     * @var string
     */
    public $event;

    /**
     * @var callable
     */
    public $listener;

    /**
     * @var int
     */
    public $priority;

    public function __construct(string $event, callable $listener, int $priority)
    {
        $this->event = $event;
        $this->listener = $listener;
        $this->priority = $priority;
    }
}
