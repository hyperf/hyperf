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

class Column
{
    public function __construct(
        protected string $schema,
        protected string $table,
        protected string $name,
        protected int $position,
        protected mixed $default,
        protected bool $isNullable,
        protected string $type,
        protected string $comment
    ) {
    }

    public function getSchema(): string
    {
        return $this->schema;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getComment(): string
    {
        return $this->comment;
    }
}
