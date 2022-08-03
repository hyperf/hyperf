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

use Hyperf\Database\Commands\Migrations\FreshCommand;
use Hyperf\Database\Commands\Migrations\GenMigrateCommand;
use Hyperf\Database\Commands\Migrations\InstallCommand;
use Hyperf\Database\Commands\Migrations\MigrateCommand;
use Hyperf\Database\Commands\Migrations\RefreshCommand;
use Hyperf\Database\Commands\Migrations\ResetCommand;
use Hyperf\Database\Commands\Migrations\RollbackCommand;
use Hyperf\Database\Commands\Migrations\StatusCommand;
use Hyperf\Database\Commands\ModelCommand;
use Hyperf\Database\Commands\Seeders\GenSeederCommand;
use Hyperf\Database\Commands\Seeders\SeedCommand;

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
        return [
            ModelCommand::class,
            GenMigrateCommand::class,
            GenSeederCommand::class,
            InstallCommand::class,
            MigrateCommand::class,
            FreshCommand::class,
            RefreshCommand::class,
            ResetCommand::class,
            RollbackCommand::class,
            StatusCommand::class,
            SeedCommand::class,
        ];
    }
}
