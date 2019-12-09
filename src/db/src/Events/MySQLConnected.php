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

class MySQLConnected
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
     * The number of milliseconds it took to execute the query.
     *
     * @var float
     */
    public $time;

    /**
     * The database connect config.
     *
     * @var array
     */
    public $config;

    /**
     * Create a new event instance.
     *
     * @param AbstractConnection $connection
     * @param float $time
     * @param array $config
     */
    public function __construct($connection, $time, $config)
    {
        $this->connection = $connection;
        $this->connectionName = $connection->getName();
        $this->time = $time;
        $this->config = $config;
    }
}
