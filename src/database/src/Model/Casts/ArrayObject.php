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

namespace Hyperf\Database\Model\Casts;

use ArrayObject as BaseArrayObject;
use Hyperf\Collection\Collection;
use Hyperf\Contract\Arrayable;
use JsonSerializable;

/**
 * @template TKey of array-key
 * @template TItem
 * @extends  BaseArrayObject<TKey, TItem>
 */
class ArrayObject extends BaseArrayObject implements Arrayable, JsonSerializable
{
    /**
     * Get a collection containing the underlying array.
     */
    public function collect(): Collection
    {
        return new Collection($this->getArrayCopy());
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }

    /**
     * Get the array that should be JSON serialized.
     */
    public function jsonSerialize(): array
    {
        return $this->getArrayCopy();
    }
}
