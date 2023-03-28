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
namespace Hyperf\Collection;

/**
 * Create a collection from the given value.
 *
 * @template TKey of array-key
 * @template TValue
 *
 * @param null|\Hyperf\Contract\Arrayable<TKey, TValue>|iterable<TKey, TValue> $value
 * @return Collection<TKey, TValue>
 */
function collect($value = []): Collection
{
    return new Collection($value);
}
