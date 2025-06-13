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
use PDOStatement;

class StatementPrepared
{
    /**
     * Create a new event instance.
     *
     * @param Connection&ConnectionInterface $connection
     */
    public function __construct(public ConnectionInterface $connection, public PDOStatement $statement)
    {
    }
}
