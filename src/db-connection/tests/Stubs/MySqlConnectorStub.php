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

use Hyperf\Database\Connectors\MySqlConnector;

class MySqlConnectorStub extends MySqlConnector
{
    public function createPdoConnection($dsn, $username, $password, $options)
    {
        return new PDOStub($dsn, $username, $password, $options);
    }
}
