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

namespace Hyperf\Database\Model\Concerns;

use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\ModelNotFoundException;

trait HasUniqueStringIds
{
    /**
     * Generate a new unique ID for the model.
     *
     * @return string
     */
    abstract public function newUniqueId();

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array
     */
    public function uniqueIds()
    {
        return [$this->getKeyName()];
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param  \Hyperf\Database\Model\Model|\Hyperf\Database\Model\Relations\Relation<*, *, *>  $query
     * @param mixed $value
     * @param null|string $field
     * @return Builder
     *
     * @throws ModelNotFoundException
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if ($field && in_array($field, $this->uniqueIds()) && ! $this->isValidUniqueId($value)) {
            $this->handleInvalidUniqueId($value, $field);
        }

        if (! $field && in_array($this->getRouteKeyName(), $this->uniqueIds()) && ! $this->isValidUniqueId($value)) {
            $this->handleInvalidUniqueId($value, $field);
        }

        return parent::resolveRouteBindingQuery($query, $value, $field);
    }

    /**
     * Get the auto-incrementing key type.
     *
     * @return string
     */
    public function getKeyType()
    {
        if (in_array($this->getKeyName(), $this->uniqueIds())) {
            return 'string';
        }

        return $this->keyType;
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        if (in_array($this->getKeyName(), $this->uniqueIds())) {
            return false;
        }

        return $this->incrementing;
    }

    /**
     * Throw an exception for the given invalid unique ID.
     *
     * @param mixed $value
     * @param null|string $field
     * @return never
     *
     * @throws ModelNotFoundException
     */
    protected function handleInvalidUniqueId($value, $field)
    {
        throw (new ModelNotFoundException())->setModel(get_class($this), $value);
    }

    /**
     * Determine if given key is valid.
     *
     * @param mixed $value
     */
    abstract protected function isValidUniqueId($value): bool;
}
