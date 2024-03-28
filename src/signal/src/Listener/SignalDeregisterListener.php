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

namespace Hyperf\Signal\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\OnWorkerExit;
use Hyperf\Process\Event\AfterProcessHandle;
use Hyperf\Server\Event\AllCoroutineServersClosed;
use Hyperf\Server\Event\CoroutineServerStop;
use Hyperf\Signal\SignalManager;
use Psr\Container\ContainerInterface;

class SignalDeregisterListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            OnWorkerExit::class,
            AfterProcessHandle::class,
            CoroutineServerStop::class,
            AllCoroutineServersClosed::class,
        ];
    }

    public function process(object $event): void
    {
        $manager = $this->container->get(SignalManager::class);
        $manager->setStopped(true);
    }
}
