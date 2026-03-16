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

namespace Hyperf\Database\Events;

use Hyperf\Database\Connection;
use Hyperf\Database\ConnectionInterface;
use Throwable;

class QueryExecuted
{
    /**
     * The database connection name.
     *
     * @var string
     */
    public $connectionName;

    /**
     * @param string $sql the SQL query that was executed
     * @param array $bindings the array of query bindings
     * @param null|float $time the number of milliseconds it took to execute the query
     * @param Connection&ConnectionInterface $connection the database connection instance
     * @param null|array|int|Throwable $result the result of query
     */
    public function __construct(
        public string $sql,
        public array $bindings,
        public ?float $time,
        public ConnectionInterface $connection,
        public mixed $result = null
    ) {
        $this->connectionName = $connection->getName();
    }
}
