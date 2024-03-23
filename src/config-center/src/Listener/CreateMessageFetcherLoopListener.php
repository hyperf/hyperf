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

namespace Hyperf\ConfigCenter\Listener;

use Hyperf\Command\Event\BeforeHandle;
use Hyperf\ConfigCenter\Mode;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Server\Event\MainCoroutineServerStart;

class CreateMessageFetcherLoopListener extends OnPipeMessageListener
{
    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
            BeforeProcessHandle::class,
            BeforeHandle::class,
            MainCoroutineServerStart::class,
        ];
    }

    public function process(object $event): void
    {
        $mode = strtolower($this->config->get('config_center.mode', Mode::PROCESS));
        if ($mode === Mode::COROUTINE) {
            $instance = $this->createDriverInstance();
            $instance && $instance->createMessageFetcherLoop();
        }
    }
}
