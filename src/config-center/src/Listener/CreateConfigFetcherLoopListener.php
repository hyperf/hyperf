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
use Hyperf\Contract\ConfigInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Hyperf\Utils\ApplicationContext;
use Swoole\Server;

class CreateConfigFetcherLoopListener extends OnPipeMessageListener
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

    public function process(object $event)
    {
        $mode = $this->config->get('config_center.mode', Mode::PROCESS);
        if ($mode === Mode::PROCESS && $event instanceof BeforeProcessHandle && $event->process->name === 'config-center-fetcher') {
            $properties = [];
            $server = ApplicationContext::getContainer()->get(Server::class);
            $properties['setServer'] = $server;
            $instance = $this->createDriverInstance($properties);
        }
        if ($mode === Mode::COROUTINE && ! $event instanceof BeforeProcessHandle) {
            $instance = $this->createDriverInstance();
        }
        isset($instance) && $instance->createConfigFetcherLoop();
    }
}
