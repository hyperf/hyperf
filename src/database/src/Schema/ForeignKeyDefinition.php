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

namespace Hyperf\Database\Schema;

use Hyperf\Support\Fluent;

/**
 * @method ForeignKeyDefinition references(array|string $columns) Specify the referenced column(s)
 * @method ForeignKeyDefinition on(string $table) Specify the referenced table
 * @method ForeignKeyDefinition onDelete(string $action) Add an ON DELETE action
 * @method ForeignKeyDefinition onUpdate(string $action) Add an ON UPDATE action
 * @method ForeignKeyDefinition deferrable(bool $value = true) Set the foreign key as deferrable (PostgreSQL)
 * @method ForeignKeyDefinition initiallyImmediate(bool $value = true) Set the default time to check the constraint (PostgreSQL)
 */
class ForeignKeyDefinition extends Fluent
{
    /**
     * Indicate that updates should cascade.
     */
    public function cascadeOnUpdate(): static
    {
        return $this->onUpdate('cascade');
    }

    /**
     * Indicate that updates should be restricted.
     */
    public function restrictOnUpdate(): static
    {
        return $this->onUpdate('restrict');
    }

    /**
     * Indicate that updates should set the foreign key value to null.
     */
    public function nullOnUpdate(): static
    {
        return $this->onUpdate('set null');
    }

    /**
     * Indicate that updates should have "no action".
     */
    public function noActionOnUpdate(): static
    {
        return $this->onUpdate('no action');
    }

    /**
     * Indicate that deletes should cascade.
     */
    public function cascadeOnDelete(): static
    {
        return $this->onDelete('cascade');
    }

    /**
     * Indicate that deletes should be restricted.
     */
    public function restrictOnDelete(): static
    {
        return $this->onDelete('restrict');
    }

    /**
     * Indicate that deletes should set the foreign key value to null.
     */
    public function nullOnDelete(): static
    {
        return $this->onDelete('set null');
    }

    /**
     * Indicate that deletes should have "no action".
     */
    public function noActionOnDelete(): static
    {
        return $this->onDelete('no action');
    }
}
