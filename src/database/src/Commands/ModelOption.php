<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
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

    /**
     * @return string
     */
    public function getUses(): string
    {
        return $this->uses;
    }

    /**
     * @param string $uses
     * @return ModelOption
     */
    public function setUses(string $uses): ModelOption
    {
        $this->uses = $uses;
        return $this;
    }
}
