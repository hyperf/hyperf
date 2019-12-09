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

use Hyperf\DB\AbstractConnection;

abstract class ConnectionEvent
{
    /**
     * The database connection instance.
     *
     * @var AbstractConnection
     */
    public $connection;

    /**
     * The name of the connection.
     *
     * @var null|string
     */
    public $connectionName;

    /**
     * Create a new event instance.
     *
     * @param AbstractConnection $connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->connectionName = $connection->getName();
    }
}
