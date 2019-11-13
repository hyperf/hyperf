<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Database\Commands;

class ModelOption
{
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

    public function getPool(): string
    {
        return $this->pool;
    }

    public function setPool(string $pool): ModelOption
    {
        $this->pool = $pool;
        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): ModelOption
    {
        $this->path = $path;
        return $this;
    }

    public function isForceCasts(): bool
    {
        return $this->forceCasts;
    }

    public function setForceCasts(bool $forceCasts): ModelOption
    {
        $this->forceCasts = $forceCasts;
        return $this;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): ModelOption
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function getInheritance(): string
    {
        return $this->inheritance;
    }

    public function setInheritance(string $inheritance): ModelOption
    {
        $this->inheritance = $inheritance;
        return $this;
    }

    public function getUses(): string
    {
        return $this->uses;
    }

    public function setUses(string $uses): ModelOption
    {
        $this->uses = $uses;
        return $this;
    }

    public function isRefreshFillable(): bool
    {
        return $this->refreshFillable;
    }

    public function setRefreshFillable(bool $refreshFillable): ModelOption
    {
        $this->refreshFillable = $refreshFillable;
        return $this;
    }

    public function getTableMapping(): array
    {
        return $this->tableMapping;
    }

    public function setTableMapping(array $tableMapping): ModelOption
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

    public function setIgnoreTables(array $ignoreTables): ModelOption
    {
        $this->ignoreTables = $ignoreTables;
        return $this;
    }

    public function isWithComments(): bool
    {
        return $this->withComments;
    }

    public function setWithComments(bool $withComments): ModelOption
    {
        $this->withComments = $withComments;
        return $this;
    }
}
