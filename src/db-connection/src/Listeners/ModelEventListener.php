<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DbConnection\Listeners;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Database\Model\Events\Event;
use Hyperf\Event\Contract\ListenerInterface;

/**
 * @Listener
 */
class ModelEventListener implements ListenerInterface
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
        return $event->handle();
    }
}
