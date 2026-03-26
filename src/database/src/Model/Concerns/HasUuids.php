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

use Hyperf\Stringable\Str;

trait HasUuids
{
    /**
     * Generate a new UUID for the model.
     *
     * @return string
     */
    public function newUniqueId()
    {
        return (string) Str::uuidv7();
    }

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
     * Initialize the model with unique identifiers.
     *
     * @param array $columns
     * @return void
     */
    protected function initialize(array $columns): void
    {
        $uniqueIds = $this->uniqueIds();

        foreach ($columns as $column) {
            if (in_array($column, $uniqueIds, true) && ($this->{$column} === null || $this->{$column} === '')) {
                $this->{$column} = $this->newUniqueId();
            }
        }
    }

    public function save(array $options = []): bool
    {
        if (! $this->exists) {
            $this->initialize($this->uniqueIds());
        }

        return parent::save($options);
    }
}
