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
namespace Hyperf\Database\Exception;

use Exception;
use Hyperf\Utils\Str;
use PDOException;

class QueryException extends PDOException
{
    /**
     * Create a new query exception instance.
     *
     * @param string $sql the SQL for the query
     * @param array $bindings the bindings for the query
     */
    public function __construct(protected string $sql, protected array $bindings, Exception $previous)
    {
        parent::__construct('', 0, $previous);

        $this->code = $previous->getCode();
        $this->message = $this->formatMessage($sql, $bindings, $previous);

        if ($previous instanceof PDOException) {
            $this->errorInfo = $previous->errorInfo;
        }
    }

    /**
     * Get the SQL for the query.
     *
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * Get the bindings for the query.
     *
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * Format the SQL error message.
     *
     * @param string $sql
     * @param array $bindings
     * @param \Exception $previous
     * @return string
     */
    protected function formatMessage($sql, $bindings, $previous)
    {
        return $previous->getMessage() . ' (SQL: ' . Str::replaceArray('?', $bindings, $sql) . ')';
    }
}
