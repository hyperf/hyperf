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

namespace Hyperf\DB;

use Hyperf\Database\DetectsDeadlocks;
use Hyperf\Database\DetectsLostConnections;
use Hyperf\Pool\Connection;

abstract class AbstractConnection extends Connection implements ConnectionInterface
{
    use DetectsDeadlocks;
    use DetectsLostConnections;

    /**
     * The number of active transactions.
     *
     * @var int
     */
    protected $transactions = 0;
}
