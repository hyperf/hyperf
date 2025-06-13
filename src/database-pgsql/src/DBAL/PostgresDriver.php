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

namespace Hyperf\Database\PgSQL\DBAL;

use Doctrine\DBAL\Driver\AbstractPostgreSQLDriver;
use InvalidArgumentException;
use Swoole\Coroutine\PostgreSQL;

class PostgresDriver extends AbstractPostgreSQLDriver
{
    /**
     * Create a new database connection.
     */
    public function connect(array $params)
    {
        if (! isset($params['pdo']) || ! $params['pdo'] instanceof PostgreSQL) {
            throw new InvalidArgumentException('The "pdo" property must be required.');
        }

        return new Connection($params['pdo']);
    }
}
