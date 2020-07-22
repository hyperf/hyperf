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
namespace Hyperf\Di\Definition;

class ScanConfig
{
    /**
     * @var array
     */
    protected $dirs;

    /**
     * @var array
     */
    protected $ignoreAnnotations;

    /**
     * @var array
     */
    protected $collectors;

    public function __construct(array $dirs = [], array $ignoreAnnotations = [], array $collectors = [])
    {
        $this->dirs = $dirs;
        $this->ignoreAnnotations = $ignoreAnnotations;
        $this->collectors = $collectors;
    }

    public function getDirs(): array
    {
        return $this->dirs;
    }

    public function setDirs(array $dirs): self
    {
        $this->dirs = $dirs;
        return $this;
    }

    public function getIgnoreAnnotations(): array
    {
        return $this->ignoreAnnotations;
    }

    public function setIgnoreAnnotations(array $ignoreAnnotations): self
    {
        $this->ignoreAnnotations = $ignoreAnnotations;
        return $this;
    }

    public function getCollectors(): array
    {
        return $this->collectors;
    }

    public function setCollectors(array $collectors): self
    {
        $this->collectors = $collectors;
        return $this;
    }
}
