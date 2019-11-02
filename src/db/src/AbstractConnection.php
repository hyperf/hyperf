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

namespace Hyperf\DB;

use Hyperf\Contract\ConnectionInterface;
use Hyperf\Pool\Connection as BaseConnection;

abstract class AbstractConnection extends BaseConnection implements ConnectionInterface
{
    abstract public function beginTransaction();

    abstract public function commit();

    abstract public function rollback();

    abstract public function getErrorCode();

    abstract public function getErrorInfo();

    abstract public function getLastInsertId();

    abstract public function prepare(string $sql, array $data = [], array $options = []): bool;

    abstract public function query(string $sql): ?array;
}
