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
namespace Hyperf\ModelListener\Listener;

use Hyperf\Database\Model\Events\Event;
use Hyperf\Event\Contract\ListenerInterface;

class ModelHookEventListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            Event::class,
        ];
    }

    /**
     * @param Event $event
     */
    public function process(object $event)
    {
        $event->handle();
    }
}
