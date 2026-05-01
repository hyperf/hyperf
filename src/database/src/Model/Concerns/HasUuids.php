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
     */
    public function newUniqueId(): string
    {
        return (string) Str::uuidv7();
    }

    /**
     * Get the columns that should receive a unique identifier.
     */
    public function uniqueIds(): array
    {
        return [$this->getKeyName()];
    }

    /**
     * Get the auto-incrementing key type.
     */
    public function getKeyType(): string
    {
        if (in_array($this->getKeyName(), $this->uniqueIds())) {
            return 'string';
        }

        return $this->keyType;
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     */
    public function getIncrementing(): bool
    {
        if (in_array($this->getKeyName(), $this->uniqueIds())) {
            return false;
        }

        return $this->incrementing;
    }

    /**
     * Generate UUIDs for unique-id columns before inserting.
     */
    protected function performInsert(Builder $query): bool
    {
        foreach ($this->uniqueIds() as $column) {
            if (empty($this->getAttribute($column))) {
                $this->setAttribute($column, $this->newUniqueId());
            }
        }

        return parent::performInsert($query);
    }
}
