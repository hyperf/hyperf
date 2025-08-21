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

namespace Hyperf\Stdlib;

use ReturnTypeWillChange;

use const PHP_INT_MAX;

/**
 * Serializable version of SplPriorityQueue.
 *
 * Also, provides predictable heap order for datums added with the same priority
 * (i.e., they will be emitted in the same order they are enqueued).
 *
 * @template TValue
 * @template TPriority
 * @extends \SplPriorityQueue<TPriority, TValue>
 */
class SplPriorityQueue extends \SplPriorityQueue
{
    /**
     * Seed used to ensure queue order for items of the same priority.
     */
    protected int $serial = PHP_INT_MAX;

    /**
     * Insert a value with a given priority.
     *
     * Utilizes {@var to $serial} ensure that values of equal priority are
     * emitted in the same order in which they are inserted.
     *
     * @param TValue $value
     * @param TPriority $priority
     */
    #[ReturnTypeWillChange]
    public function insert(mixed $value, mixed $priority): bool
    {
        return parent::insert($value, [$priority, $this->serial--]);
    }

    /**
     * Serialize to an array.
     *
     * Array will be priority => data pairs
     *
     * @return list<TValue>
     */
    public function toArray(): array
    {
        $array = [];
        foreach (clone $this as $item) {
            $array[] = $item;
        }
        return $array;
    }
}
