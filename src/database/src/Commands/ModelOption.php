<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
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
     * @return string
     */
    public function getPool(): string
    {
        return $this->pool;
    }

    /**
     * @param string $pool
     * @return ModelOption
     */
    public function setPool(string $pool): ModelOption
    {
        $this->pool = $pool;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return ModelOption
     */
    public function setPath(string $path): ModelOption
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return bool
     */
    public function isForceCasts(): bool
    {
        return $this->forceCasts;
    }

    /**
     * @param bool $forceCasts
     * @return ModelOption
     */
    public function setForceCasts(bool $forceCasts): ModelOption
    {
        $this->forceCasts = $forceCasts;
        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     * @return ModelOption
     */
    public function setPrefix(string $prefix): ModelOption
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @return string
     */
    public function getInheritance(): string
    {
        return $this->inheritance;
    }

    /**
     * @param string $inheritance
     * @return ModelOption
     */
    public function setInheritance(string $inheritance): ModelOption
    {
        $this->inheritance = $inheritance;
        return $this;
    }
}
