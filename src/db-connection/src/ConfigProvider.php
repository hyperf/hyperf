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

use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Connectors\MySqlConnector;
use Hyperf\Database\Migrations\MigrationRepositoryInterface;
use Hyperf\DbConnection\Aspect\TransactionAspect;
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
            'listeners' => [
                RegisterConnectionResolverListener::class,
            ],
            'aspects' => [
                TransactionAspect::class,
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
