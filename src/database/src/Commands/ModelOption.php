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
    const PROPERTY_SNAKE_CASE = 0;

    const PROPERTY_CAMEL_CASE = 1;

    /**
     * @var string
     */
    protected $pool;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var bool
     */
    protected $forceCasts;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var string
     */
    protected $inheritance;

    /**
     * @var string
     */
    protected $uses;

    /**
     * @var bool
     */
    protected $refreshFillable;

    /**
     * @var bool
     */
    protected $withComments;

    /**
     * @var array
     */
    protected $tableMapping = [];

    /**
     * @var array
     */
    protected $ignoreTables = [];

    /**
     * @var array
     */
    protected $visitors = [];

    /**
     * @var int
     */
    protected $propertyCase = self::PROPERTY_SNAKE_CASE;

    public function getPool(): string
    {
        return $this->pool;
    }

    public function setPool(string $pool): self
    {
        $this->pool = $pool;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    public function isForceCasts(): bool
    {
        return $this->forceCasts;
    }

    public function setForceCasts(bool $forceCasts): self
    {
        $this->forceCasts = $forceCasts;
        return $this;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function getInheritance(): string
    {
        return $this->inheritance;
    }

    public function setInheritance(string $inheritance): self
    {
        $this->inheritance = $inheritance;
        return $this;
    }

    public function getUses(): string
    {
        return $this->uses;
    }

    public function setUses(string $uses): self
    {
        $this->uses = $uses;
        return $this;
    }

    public function isRefreshFillable(): bool
    {
        return $this->refreshFillable;
    }

    public function setRefreshFillable(bool $refreshFillable): self
    {
        $this->refreshFillable = $refreshFillable;
        return $this;
    }

    public function getTableMapping(): array
    {
        return $this->tableMapping;
    }

    public function setTableMapping(array $tableMapping): self
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

    public function setIgnoreTables(array $ignoreTables): self
    {
        $this->ignoreTables = $ignoreTables;
        return $this;
    }

    public function isWithComments(): bool
    {
        return $this->withComments;
    }

    public function setWithComments(bool $withComments): self
    {
        $this->withComments = $withComments;
        return $this;
    }

    public function getVisitors(): array
    {
        return $this->visitors;
    }

    public function setVisitors(array $visitors): self
    {
        $this->visitors = $visitors;
        return $this;
    }

    public function isCamelCase(): bool
    {
        return $this->propertyCase === self::PROPERTY_CAMEL_CASE;
    }

    public function setPropertyCase($propertyCase): self
    {
        $this->propertyCase = (int) $propertyCase;
        return $this;
    }
}
