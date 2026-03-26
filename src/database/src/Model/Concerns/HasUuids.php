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
     * Boot the HasUuids trait for a model.
     *
     * This will ensure that UUIDs are generated only for new models that
     * are being created, avoiding unnecessary work during model hydration.
     *
     * @return void
     */
    protected static function bootHasUuids(): void
    {
        static::creating(function ($model): void {
            $columns = $model->uniqueIds();
            foreach ($columns as $column) {
                if (in_array($column, $columns, true) && ($model->{$column} === null || $model->{$column} === '')) {
                    $model->{$column} = $model->newUniqueId();
                }
            }
        });
    }

    /**
     * Initialize the model with unique identifiers using the initialize{trait_name} method.
     *
     * This method is kept for compatibility but intentionally does not
     * generate UUIDs to avoid unnecessary work during model construction.
     *
     * @return void
     */
    protected function initializeHasUuids(): void
    {
        // UUIDs are now generated in the bootHasUuids creating listener.
    }
}

