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

namespace Hyperf\Database;

use Closure;

/**
 * An operation may consist of several commands that must share one connection.
 */
interface ConnectionOperationInterface
{
    /**
     * @internal the callback must not retain the physical connection or its handles
     * @param Closure(ConnectionInterface): mixed $callback
     */
    public function runOperation(Closure $callback): mixed;
}
