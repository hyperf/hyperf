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
    /**
     * @var string
     */
    protected $schema;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var mixed
     */
    protected $default;

    /**
     * @var bool
     */
    protected $isNullable;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $comment;

    public function __construct(string $schema, string $table, string $name, int $position, $default, bool $isNullable, string $type, string $comment)
    {
        $this->schema = $schema;
        $this->table = $table;
        $this->name = $name;
        $this->position = $position;
        $this->default = $default;
        $this->isNullable = $isNullable;
        $this->type = $type;
        $this->comment = $comment;
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

    public function getDefault()
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
