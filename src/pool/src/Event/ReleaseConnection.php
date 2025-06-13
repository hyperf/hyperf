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

namespace Hyperf\Pool\Event;

use Hyperf\Contract\ConnectionInterface;

class ReleaseConnection
{
    public function __construct(public ConnectionInterface $connection)
    {
    }
}
