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

namespace Hyperf\AsyncQueue;

use Hyperf\AsyncQueue\Aspect\AsyncQueueAspect;
use Hyperf\AsyncQueue\Command\DynamicReloadMessageCommand;
use Hyperf\AsyncQueue\Command\FlushFailedMessageCommand;
use Hyperf\AsyncQueue\Command\InfoCommand;
use Hyperf\AsyncQueue\Command\ReloadFailedMessageCommand;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'aspects' => [
                AsyncQueueAspect::class,
            ],
            'commands' => [
                FlushFailedMessageCommand::class,
                InfoCommand::class,
                ReloadFailedMessageCommand::class,
                DynamicReloadMessageCommand::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for async queue.',
                    'source' => __DIR__ . '/../publish/async_queue.php',
                    'destination' => BASE_PATH . '/config/autoload/async_queue.php',
                ],
            ],
        ];
    }
}
