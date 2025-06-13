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

namespace Hyperf\Database\Commands;

class ModelOption
{
    public const PROPERTY_SNAKE_CASE = 0;

    public const PROPERTY_CAMEL_CASE = 1;

    protected ?string $pool = null;

    protected ?string $path = null;

    protected ?bool $forceCasts = null;

    protected ?string $prefix = null;

    protected ?string $inheritance = null;

    protected ?string $uses = null;

    protected ?bool $refreshFillable = null;

    protected ?bool $withComments = null;

    protected ?bool $withIde = null;

    protected array $tableMapping = [];

    protected array $ignoreTables = [];

    protected array $visitors = [];

    protected int $propertyCase = self::PROPERTY_SNAKE_CASE;

    public function getPool(): string
    {
        return $this->pool;
    }

    public function setPool(string $pool): static
    {
        $this->pool = $pool;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): static
    {
        $this->path = $path;
        return $this;
    }

    public function isForceCasts(): bool
    {
        return $this->forceCasts;
    }

    public function setForceCasts(bool $forceCasts): static
    {
        $this->forceCasts = $forceCasts;
        return $this;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function getInheritance(): string
    {
        return $this->inheritance;
    }

    public function setInheritance(string $inheritance): static
    {
        $this->inheritance = $inheritance;
        return $this;
    }

    public function getUses(): string
    {
        return $this->uses;
    }

    public function setUses(string $uses): static
    {
        $this->uses = $uses;
        return $this;
    }

    public function isRefreshFillable(): bool
    {
        return $this->refreshFillable;
    }

    public function setRefreshFillable(bool $refreshFillable): static
    {
        $this->refreshFillable = $refreshFillable;
        return $this;
    }

    public function getTableMapping(): array
    {
        return $this->tableMapping;
    }

    public function setTableMapping(array $tableMapping): static
    {
        foreach ($tableMapping as $item) {
            [$key, $name] = explode(':', $item);
            $this->tableMapping[$key] = $name;
        }

        return $this;
    }

    public function getIgnoreTables(): array
    {
        return $this->ignoreTables;
    }

    public function setIgnoreTables(array $ignoreTables): static
    {
        $this->ignoreTables = $ignoreTables;
        return $this;
    }

    public function isWithComments(): bool
    {
        return $this->withComments;
    }

    public function setWithComments(bool $withComments): static
    {
        $this->withComments = $withComments;
        return $this;
    }

    public function isWithIde(): bool
    {
        return $this->withIde;
    }

    public function setWithIde(bool $withIde): ModelOption
    {
        $this->withIde = $withIde;
        return $this;
    }

    public function getVisitors(): array
    {
        return $this->visitors;
    }

    public function setVisitors(array $visitors): static
    {
        $this->visitors = $visitors;
        return $this;
    }

    public function isCamelCase(): bool
    {
        return $this->propertyCase === self::PROPERTY_CAMEL_CASE;
    }

    public function setPropertyCase($propertyCase): static
    {
        $this->propertyCase = (int) $propertyCase;
        return $this;
    }
}
