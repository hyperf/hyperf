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
namespace Hyperf\Server\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Server\Event\CoroutineServerStart;
use Hyperf\Utils\Context;

class StoreServerNameListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            CoroutineServerStart::class,
        ];
    }

    /**
     * @param CoroutineServerStart $event
     */
    public function process(object $event)
    {
        $serverName = $event->name;
        if (! $serverName) {
            return;
        }
        Context::set('__hyperf__.server.name', $serverName);
    }
}
