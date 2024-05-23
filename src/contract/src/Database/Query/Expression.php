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

namespace Hyperf\Contract\Database\Query;

use Hyperf\Database\Grammar;

interface Expression
{
    /**
     * Get the value of the expression.
     * @return float|int|string
     */
    public function getValue(Grammar $grammar);
}
