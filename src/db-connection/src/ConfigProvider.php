<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DbConnection;

use Hyperf\Database\Commands\ModelCommand;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Connectors\MySqlConnector;
use Hyperf\Database\Commands\Migrations\GenMigrateCommand;
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
            ],
            'commands' => [
                ModelCommand::class,
                GenMigrateCommand::class,
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
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
