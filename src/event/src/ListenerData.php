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
    public const DEFAULT_PRIORITY = 0;

    /**
     * @var callable
     */
    public $listener;

    public function __construct(public string $event, callable $listener, public int $priority)
    {
        $this->listener = $listener;
    }
}
