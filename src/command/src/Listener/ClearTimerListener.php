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
namespace Hyperf\Command\Listener;

use Hyperf\Command\Event\AfterExecute;
use Hyperf\Event\Contract\ListenerInterface;
use Swoole\Timer;

class ClearTimerListener implements ListenerInterface
{
    public function listen(): array
    {
        return [
            AfterExecute::class,
        ];
    }

    public function process(object $event)
    {
        Timer::clearAll();
    }
}
