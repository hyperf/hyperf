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

namespace Hyperf\Redis;

use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Connectors\MySqlConnector;
use Hyperf\Database\Connectors\PostgresConnector;
use Hyperf\Database\Connectors\SQLiteConnector;
use Hyperf\Database\Connectors\SqlServerConnector;
use Hyperf\Redis\RedisConnection;
use Hyperf\Redis\Pool\PoolFactory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                \Redis::class => Redis::class,
                PoolFactory::class => PoolFactory::class,
            ],
            'commands' => [
            ],
            'scan' => [
                'paths' => [],
            ],
        ];
    }
}
