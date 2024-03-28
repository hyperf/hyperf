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

namespace Hyperf\Database;

use PDO;
use PDOStatement;

class MySqlBitConnection extends MySqlConnection
{
    public function bindValues(PDOStatement $statement, array $bindings): void
    {
        foreach ($bindings as $key => $value) {
            $type = PDO::PARAM_STR;
            if (in_array($value, [0, 1], true)) {
                $type = PDO::PARAM_INT;
            }
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                $type
            );
        }
    }
}
