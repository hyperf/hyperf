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

class PDOStatementStub extends PDOStatement
{
    public $statement;

    public function __construct($statement)
    {
        $this->statement = $statement;
    }

    public function execute($input_parameters = null)
    {
        return true;
    }

    public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        parent::fetch($fetch_style, $cursor_orientation, $cursor_offset);
    }

    public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
    {
        parent::bindParam($parameter, $variable, $data_type, $length, $driver_options);
    }

    public function bindColumn($column, &$param, $type = null, $maxlen = null, $driverdata = null)
    {
        parent::bindColumn($column, $param, $type, $maxlen, $driverdata);
    }

    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
    {
        parent::bindValue($parameter, $value, $data_type);
    }

    public function rowCount()
    {
        parent::rowCount();
    }

    public function fetchColumn($column_number = 0)
    {
        parent::fetchColumn($column_number);
    }

    public function fetchAll($fetch_style = null, $fetch_argument = null, $ctor_args = null)
    {
        return [];
    }

    public function fetchObject($class_name = 'stdClass', $ctor_args = null)
    {
        parent::fetchObject($class_name, $ctor_args);
    }

    public function errorCode()
    {
        parent::errorCode();
    }

    public function errorInfo()
    {
        parent::errorInfo();
    }

    public function setAttribute($attribute, $value)
    {
        parent::setAttribute($attribute, $value);
    }

    public function getAttribute($attribute)
    {
        parent::getAttribute($attribute);
    }

    public function columnCount()
    {
        parent::columnCount();
    }

    public function getColumnMeta($column)
    {
        parent::getColumnMeta($column);
    }

    public function setFetchMode($mode, $params = null)
    {
        parent::setFetchMode($mode, $params);
    }

    public function nextRowset()
    {
        parent::nextRowset();
    }

    public function closeCursor()
    {
        parent::closeCursor();
    }

    public function debugDumpParams()
    {
        parent::debugDumpParams();
    }
}
