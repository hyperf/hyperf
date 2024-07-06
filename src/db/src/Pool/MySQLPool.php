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

namespace Hyperf\DB\Pool;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\DB\MySQLConnection;

class MySQLPool extends Pool
{
    protected function createConnection(): ConnectionInterface
    {
        return new MySQLConnection($this->container, $this, $this->config);
    }
}
