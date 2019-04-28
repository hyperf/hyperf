<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DbConnection\Listener;

use Hyperf\Database\Model\Events\Event;
use Hyperf\Event\Annotation\Listener;
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
