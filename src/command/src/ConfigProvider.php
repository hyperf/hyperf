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

namespace Hyperf\Command;

use Hyperf\Command\Listener\RegisterCommandListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'listeners' => [
                RegisterCommandListener::class,
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The console route file of command.',
                    'source' => __DIR__ . '/../publish/console.php',
                    'destination' => Console::ROUTE,
                ],
            ],
        ];
    }
}
