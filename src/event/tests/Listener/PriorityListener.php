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

namespace HyperfTest\Event\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use HyperfTest\Event\Event\PriorityEvent;

class PriorityListener implements ListenerInterface
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function listen(): array
    {
        return [
            PriorityEvent::class,
        ];
    }

    /**
     * @param PriorityEvent $event
     */
    public function process(object $event): void
    {
        PriorityEvent::$result[] = $this->id;
    }
}
