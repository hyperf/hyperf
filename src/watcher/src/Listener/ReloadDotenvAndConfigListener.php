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

class ReloadDotenvAndConfigListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
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
