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

namespace Hyperf\DB\Events;

use Hyperf\DB\MySQLConnection;
use Hyperf\DB\PDOConnection;
use PDOStatement;
use Swoole\Coroutine\MySQL\Statement;

class StatementPrepared
{
    /**
     * The database connection instance.
     *
     * @var MySQLConnection|PDOConnection
     */
    public $connection;

    /**
     * The name of the connection.
     *
     * @var null|string
     */
    public $connectionName;

    /**
     * The PDO / MySQL statement.
     *
     * @var PDOStatement|Statement
     */
    public $statement;

    /**
     * Create a new event instance.
     *
     * @param MySQLConnection|PDOConnection $connection
     * @param PDOStatement|Statement $statement
     */
    public function __construct($connection, $statement)
    {
        $this->connection = $connection;
        $this->connectionName = $connection->getName();
        $this->statement = $statement;
    }
}
