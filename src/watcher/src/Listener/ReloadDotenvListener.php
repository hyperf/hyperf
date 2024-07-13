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

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Support\DotenvManager;
use Hyperf\Watcher\Event\BeforeServerRestart;
use Psr\Container\ContainerInterface;

final class ReloadDotenvListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            BeforeServerRestart::class,
        ];
    }

    public function process(object $event): void
    {
        $this->reloadDotenv();
    }

    private function reloadDotenv(): void
    {
        if (file_exists(BASE_PATH . '/.env')) {
            DotenvManager::reload([BASE_PATH]);
        }
    }
}
