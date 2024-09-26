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

class PDOStatementStubPHP8 extends PDOStatement
{
    public $statement;

    public function __construct($statement)
    {
        $this->statement = $statement;
    }

    public function execute($input_parameters = null): bool
    {
        return true;
    }

    public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0): mixed
    {
        return parent::fetch($fetch_style, $cursor_orientation, $cursor_offset);
    }

    public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null): bool
    {
        return parent::bindParam($parameter, $variable, $data_type, $length, $driver_options);
    }

    public function bindColumn($column, &$param, $type = null, $maxlen = null, $driverdata = null): bool
    {
        return parent::bindColumn($column, $param, $type, $maxlen, $driverdata);
    }

    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR): bool
    {
        return parent::bindValue($parameter, $value, $data_type);
    }

    public function rowCount(): int
    {
        return parent::rowCount();
    }

    public function fetchColumn($column_number = 0): mixed
    {
        return parent::fetchColumn($column_number);
    }

    public function fetchAll(int $mode = PDO::FETCH_BOTH, mixed ...$args): array
    {
        return [];
    }

    public function fetchObject($class_name = 'stdClass', $ctor_args = null): object
    {
        return parent::fetchObject($class_name, $ctor_args);
    }

    public function errorCode(): ?string
    {
        return parent::errorCode();
    }

    public function errorInfo(): array
    {
        return parent::errorInfo();
    }

    public function setAttribute($attribute, $value): bool
    {
        return parent::setAttribute($attribute, $value);
    }

    public function getAttribute($attribute): mixed
    {
        return parent::getAttribute($attribute);
    }

    public function columnCount(): int
    {
        return parent::columnCount();
    }

    public function getColumnMeta($column): array
    {
        return parent::getColumnMeta($column);
    }

    public function setFetchMode($mode, $className = null, ...$params)
    {
        return true;
    }

    public function nextRowset(): bool
    {
        return parent::nextRowset();
    }

    public function closeCursor(): bool
    {
        return parent::closeCursor();
    }

    public function debugDumpParams(): ?bool
    {
        return parent::debugDumpParams();
    }
}
