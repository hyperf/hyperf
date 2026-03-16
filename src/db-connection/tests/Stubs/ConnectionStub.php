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

namespace HyperfTest\DbConnection\Stubs;

use Hyperf\DbConnection\Connection;

class ConnectionStub extends Connection
{
    /**
     * Refresh pdo and readPdo for current connection.
     */
    protected function refresh(\Hyperf\Database\Connection $connection)
    {
        $connection->disconnect();
        $connection->setPdo(new PDOStub('', '', '', []));
        $connection->setReadPdo(new PDOStub('', '', '', []));
    }
}
