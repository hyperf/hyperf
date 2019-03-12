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

use Hyperf\DbConnection\Pool\PoolFactory;
use Hyperf\Database\Commands\ModelCommand;
use Hyperf\Database\Connectors\MySqlConnector;
use Hyperf\Database\Connectors\SQLiteConnector;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Connectors\PostgresConnector;
use Hyperf\Database\Connectors\SqlServerConnector;

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
            ],
            'scan' => [
                'paths' => [
                    __DIR__,
                ],
            ],
        ];
    }
}
