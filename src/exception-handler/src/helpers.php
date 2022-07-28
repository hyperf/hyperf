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

use Hyperf\ExceptionHandler\Exception\VarDumperAbort;

if (false === function_exists('d')) {
    /**
     * @throws VarDumperAbort
     */
    function d(...$vars)
    {
        throw new VarDumperAbort($vars);
    }
}