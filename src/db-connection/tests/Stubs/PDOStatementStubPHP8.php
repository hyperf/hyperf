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

    public function execute($params = null): bool
    {
        return true;
    }

    public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0): mixed
    {
        return null;
    }

    public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null): bool
    {
        return true;
    }

    public function bindColumn($column, &$param, $type = null, $maxlen = null, $driverdata = null): bool
    {
        return true;
    }

    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR): bool
    {
        return true;
    }

    public function rowCount(): int
    {
        return 0;
    }

    public function fetchColumn($column_number = 0): mixed
    {
        return false;
    }

    public function fetchAll(int $mode = PDO::FETCH_BOTH, mixed ...$args): array
    {
        return [];
    }

    #[ReturnTypeWillChange]
    public function fetchObject($class_name = 'stdClass', $ctor_args = null): bool|object
    {
        return false;
    }

    public function errorCode(): ?string
    {
        return null;
    }

    public function errorInfo(): array
    {
        return [];
    }

    public function setAttribute($attribute, $value): bool
    {
        return true;
    }

    public function getAttribute($attribute): mixed
    {
        return '';
    }

    public function columnCount(): int
    {
        return 0;
    }

    #[ReturnTypeWillChange]
    public function getColumnMeta($column): array
    {
        return [];
    }

    public function setFetchMode($mode, $className = null, ...$params)
    {
        return true;
    }

    public function nextRowset(): bool
    {
        return true;
    }

    public function closeCursor(): bool
    {
        return true;
    }

    public function debugDumpParams(): ?bool
    {
        return null;
    }
}
