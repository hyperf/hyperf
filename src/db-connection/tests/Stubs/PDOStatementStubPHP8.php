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

use PDO;
use PDOStatement;
use ReturnTypeWillChange;

class PDOStatementStubPHP8 extends PDOStatement
{
    public $statement;

    public function __construct($statement)
    {
        $this->statement = $statement;
    }

    #[ReturnTypeWillChange]
    public function execute(?array $params = null): bool
    {
        return true;
    }

    #[ReturnTypeWillChange]
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    {
        return null;
    }

    #[ReturnTypeWillChange]
    public function bindParam(int|string $param, mixed &$var, int $type = PDO::PARAM_STR, ?int $maxLength = 0, mixed $driverOptions = null): bool
    {
        return true;
    }

    #[ReturnTypeWillChange]
    public function bindColumn(int|string $column, mixed &$var, int $type = PDO::PARAM_STR, int $maxLength = 0, mixed $driverOptions = null): bool
    {
        return true;
    }

    #[ReturnTypeWillChange]
    public function bindValue(int|string $param, mixed $value, int $type = PDO::PARAM_STR): bool
    {
        return true;
    }

    #[ReturnTypeWillChange]
    public function fetchAll(int $mode = PDO::FETCH_BOTH, mixed ...$args): array
    {
        return [];
    }

    #[ReturnTypeWillChange]
    public function setFetchMode($mode, $className = null, ...$params)
    {
        return true;
    }
}
