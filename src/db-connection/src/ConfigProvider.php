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
namespace Hyperf\DbConnection;

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
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Connectors\MySqlConnector;
use Hyperf\Database\Migrations\MigrationRepositoryInterface;
use Hyperf\DbConnection\Listener\RegisterConnectionResolverListener;
use Hyperf\DbConnection\Pool\PoolFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                PoolFactory::class => PoolFactory::class,
                ConnectionFactory::class => ConnectionFactory::class,
                ConnectionResolverInterface::class => ConnectionResolver::class,
                'db.connector.mysql' => MySqlConnector::class,
                MigrationRepositoryInterface::class => DatabaseMigrationRepositoryFactory::class,
            ],
            'commands' => [
                ModelCommand::class,
                GenMigrateCommand::class,
                InstallCommand::class,
                MigrateCommand::class,
                FreshCommand::class,
                RefreshCommand::class,
                ResetCommand::class,
                RollbackCommand::class,
                StatusCommand::class,
                GenSeederCommand::class,
                SeedCommand::class,
            ],
            'listeners' => [
                RegisterConnectionResolverListener::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for database.',
                    'source' => __DIR__ . '/../publish/databases.php',
                    'destination' => BASE_PATH . '/config/autoload/databases.php',
                ],
                [
                    'id' => 'query-listener',
                    'description' => 'The listener of database to record log.',
                    'source' => __DIR__ . '/../publish/DbQueryExecutedListener.php',
                    'destination' => BASE_PATH . '/app/Listener/DbQueryExecutedListener.php',
                ],
            ],
        ];
    }
}
