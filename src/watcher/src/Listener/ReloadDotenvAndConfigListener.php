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

namespace Hyperf\Watcher\Listener;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Support\DotenvManager;
use Hyperf\Watcher\Event\BeforeServerRestart;
use Psr\Container\ContainerInterface;
use Swoole\Atomic;

class ReloadDotenvAndConfigListener implements ListenerInterface
{
    protected static Atomic $restartCounter;

    public function __construct(protected ContainerInterface $container)
    {
        static::$restartCounter = new Atomic(0);
    }

    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
            BeforeServerRestart::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof BeforeWorkerStart
            && $event->workerId === 0
            && static::$restartCounter->get() === 0
        ) {
            static::$restartCounter->add();
            return;
        }

        static::$restartCounter->add();

        $this->reloadDotenv();
        $this->reloadConfig();
    }

    protected function reloadConfig(): void
    {
        if (! method_exists($this->container, 'unbind')) {
            return;
        }

        $this->container->unbind(ConfigInterface::class);
    }

    protected function reloadDotenv(): void
    {
        DotenvManager::reload([BASE_PATH]);
    }
}
