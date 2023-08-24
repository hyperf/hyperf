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
namespace Hyperf\Support;

use const PHP_INT_MAX;

class SplPriorityQueue extends \SplPriorityQueue
{
    protected $serial = PHP_INT_MAX;

    public function insert($value, $priority): void
    {
        $priority = [$priority, $this->serial--];
        parent::insert($value, $priority);
    }
}
