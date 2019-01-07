<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DbConnection;

use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Connectors\MySqlConnector;
use Hyperf\Database\Connectors\PostgresConnector;
use Hyperf\Database\Connectors\SQLiteConnector;
use Hyperf\Database\Connectors\SqlServerConnector;
use Hyperf\DbConnection\Pool\PoolFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                PoolFactory::class => PoolFactory::class,
                ConnectionFactory::class => ConnectionFactory::class,
                'db.connector.mysql' => MySqlConnector::class,
                'db.connector.pgsql' => PostgresConnector::class,
                'db.connector.sqlite' => SQLiteConnector::class,
                'db.connector.sqlsrv' => SqlServerConnector::class,
            ],
            'commands' => [
            ],
            'scan' => [
                'paths' => [
                    'vendor/hyperf/db-connection/src',
                    'vendor/hyperf/hyperf/src/db-connection/src'
                ],
            ],
        ];
    }
}
