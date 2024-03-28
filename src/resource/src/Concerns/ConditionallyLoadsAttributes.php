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

namespace Hyperf\Resource\Concerns;

use Countable;
use Hyperf\Collection\Arr;
use Hyperf\Resource\Json\JsonResource;
use Hyperf\Resource\Value\MergeValue;
use Hyperf\Resource\Value\MissingValue;
use Hyperf\Resource\Value\PotentiallyMissing;

use function Hyperf\Support\value;

/**
 * Trait ConditionallyLoadsAttributes.
 *
 * @mixin JsonResource
 */
trait ConditionallyLoadsAttributes
{
    /**
     * Indicates if the resource's collection keys should be preserved.
     */
    public bool $preserveKeys = false;

    /**
     * Filter the given data, removing any optional values.
     */
    protected function filter(array $data): array
    {
        $index = -1;

        foreach ($data as $key => $value) {
            ++$index;

            if (is_array($value)) {
                $data[$key] = $this->filter($value);
                continue;
            }

            if (is_numeric($key) && $value instanceof MergeValue) {
                return $this->mergeData(
                    $data,
                    $index,
                    $this->filter($value->data),
                    array_values($value->data) === $value->data
                );
            }

            if ($value instanceof self && is_null($value->resource)) {
                $data[$key] = null;
            }
        }

        return $this->removeMissingValues($data);
    }

    /**
     * Merge the given data in at the given index.
     */
    protected function mergeData(array $data, int $index, array $merge, bool $numericKeys): array
    {
        if ($numericKeys) {
            return $this->removeMissingValues(array_merge(
                array_merge(array_slice($data, 0, $index, true), $merge),
                $this->filter(array_values(array_slice($data, $index + 1, null, true)))
            ));
        }

        return $this->removeMissingValues(array_slice($data, 0, $index, true) +
            $merge +
            $this->filter(array_slice($data, $index + 1, null, true)));
    }

    /**
     * Remove the missing values from the filtered data.
     */
    protected function removeMissingValues(array $data): array
    {
        $numericKeys = true;

        foreach ($data as $key => $value) {
            if (($value instanceof PotentiallyMissing && $value->isMissing())
                || ($value instanceof self
                    && $value->resource instanceof PotentiallyMissing
                    && $value->resource->isMissing())) {
                unset($data[$key]);
            } else {
                $numericKeys = $numericKeys && is_numeric($key);
            }
        }

        if ($this->preserveKeys) {
            return $data;
        }

        return $numericKeys ? array_values($data) : $data;
    }

    /**
     * Retrieve a value based on a given condition.
     *
     * @param mixed $value
     * @param mixed $default
     * @return MissingValue|mixed
     */
    protected function when(bool $condition, $value, $default = null)
    {
        if ($condition) {
            return value($value);
        }

        return func_num_args() === 3 ? value($default) : new MissingValue();
    }

    /**
     * Merge a value into the array.
     *
     * @param mixed $value
     * @return MergeValue|mixed
     */
    protected function merge($value)
    {
        return $this->mergeWhen(true, $value);
    }

    /**
     * Merge a value based on a given condition.
     *
     * @param mixed $value
     * @return MergeValue|mixed
     */
    protected function mergeWhen(bool $condition, $value)
    {
        return $condition ? new MergeValue(value($value)) : new MissingValue();
    }

    /**
     * Merge the given attributes.
     */
    protected function attributes(array $attributes): MergeValue
    {
        return new MergeValue(
            Arr::only($this->resource->toArray(), $attributes)
        );
    }

    /**
     * Retrieve a relationship if it has been loaded.
     *
     * @param mixed $value
     * @param mixed $default
     * @return MissingValue|mixed
     */
    protected function whenLoaded(string $relationship, $value = null, $default = null)
    {
        if (func_num_args() < 3) {
            $default = new MissingValue();
        }

        if (! $this->resource->relationLoaded($relationship)) {
            return value($default);
        }

        if (func_num_args() === 1) {
            return $this->resource->{$relationship};
        }

        if ($this->resource->{$relationship} === null) {
            return null;
        }

        return value($value);
    }

    /**
     * Execute a callback if the given pivot table has been loaded.
     *
     * @param mixed $value
     * @param mixed $default
     * @return MissingValue|mixed
     */
    protected function whenPivotLoaded(string $table, $value, $default = null)
    {
        return $this->whenPivotLoadedAs('pivot', ...func_get_args());
    }

    /**
     * Execute a callback if the given pivot table with a custom accessor has been loaded.
     *
     * @param mixed $value
     * @param mixed $default
     * @return MissingValue|mixed
     */
    protected function whenPivotLoadedAs(string $accessor, string $table, $value, $default = null)
    {
        if (func_num_args() === 3) {
            $default = new MissingValue();
        }

        return $this->when(
            $this->resource->{$accessor}
            && ($this->resource->{$accessor} instanceof $table
                || $this->resource->{$accessor}->getTable() === $table),
            ...[$value, $default]
        );
    }

    /**
     * Transform the given value if it is present.
     *
     * @param mixed $value
     * @param mixed $default
     * @return mixed
     */
    protected function transform($value, callable $callback, $default = null)
    {
        if (! $this->blank($value)) {
            return $callback($value);
        }

        if (is_callable($default)) {
            return $default($value);
        }

        return func_num_args() === 3 ? $default : new MissingValue();
    }

    protected function blank($value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        if ($value instanceof Countable) {
            return count($value) === 0;
        }

        return empty($value);
    }
}
