<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\DbSQLiteDriver;

use Hyperf\Database\Connection;
use Hyperf\DbSQLiteDriver\Connectors\SQLiteConnector;

class ConfigProvider
{
    public function __invoke(): array
    {
        Connection::resolverFor('sqlite', function ($connection, $database, $prefix, $config) {
            return new SQLiteConnection($connection, $database, $prefix, $config);
        });

        return [
            'dependencies' => [
                'db.connector.sqlite' => SQLiteConnector::class,
            ],
            'listeners' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                    'collectors' => [
                    ],
                ],
            ],
            'publish' => [
            ],
        ];
    }
}
