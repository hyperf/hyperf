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

namespace Hyperf\Devtool;

use Hyperf\Database\Commands\CommandCollector;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'commands' => [
                ...$this->getDatabaseCommands(),
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for devtool.',
                    'source' => __DIR__ . '/../publish/devtool.php',
                    'destination' => BASE_PATH . '/config/autoload/devtool.php',
                ],
            ],
        ];
    }

    private function getDatabaseCommands(): array
    {
        if (! class_exists(CommandCollector::class)) {
            return [];
        }

        return CommandCollector::getAllCommands();
    }
}
