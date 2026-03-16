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

namespace Hyperf\Watcher;

use Hyperf\Watcher\Command\WatchCommand;
use Hyperf\Watcher\Listener\ReloadDotenvListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
                WatchCommand::class,
            ],
            'listeners' => [
                ReloadDotenvListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for watcher.',
                    'source' => __DIR__ . '/../publish/watcher.php',
                    'destination' => BASE_PATH . '/.watcher.php',
                ],
            ],
        ];
    }
}
