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

namespace Hyperf\Database\PgSQL;

use Hyperf\Database\PgSQL\Connectors\PostgresConnector;
use Hyperf\Database\PgSQL\Connectors\PostgresSqlSwooleExtConnector;
use Hyperf\Database\PgSQL\Listener\RegisterConnectionListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                'db.connector.pgsql' => PostgresConnector::class,
                'db.connector.pgsql-swoole' => PostgresSqlSwooleExtConnector::class,
            ],
            'listeners' => [
                RegisterConnectionListener::class,
            ],
        ];
    }
}
