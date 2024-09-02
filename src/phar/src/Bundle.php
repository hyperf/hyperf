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

namespace Hyperf\Phar;

use ArrayIterator;
use IteratorAggregate;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Traversable;

class Bundle implements IteratorAggregate
{
    /**
     * @var Finder[]|string[]
     */
    private array $resources = [];

    /**
     * Add a file to the resource bundle.
     */
    public function addFile(string $file): static
    {
        $this->resources[] = $file;
        return $this;
    }

    /**
     * @param string[] $dirs
     */
    public function addDirs(array $dirs): static
    {
        return $this->addFinder((new Finder())->files()->ignoreVCS(true)->in($dirs));
    }

    /**
     * Add a directory package to a resource package.
     */
    public function addFinder(Finder $dir): static
    {
        $this->resources[] = $dir;
        return $this;
    }

    /**
     * Determines whether the file exists in the resource bundle.
     */
    public function checkContains(string $resource): bool
    {
        foreach ($this->resources as $containedResource) {
            if ($containedResource instanceof Finder && $this->directoryContains($containedResource, $resource)) {
                return true;
            }
            if (is_string($containedResource) && $containedResource === $resource) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns an iterator for a list of resources.
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->resources);
    }

    /**
     * Determines whether the file exists in the folder resource bundle.
     */
    private function directoryContains(Finder $dir, string $resource): bool
    {
        $resourceStrLength = strlen($resource);
        foreach ($dir as $containedResource) {
            /* @var $containedResource SplFileInfo */
            if (substr($containedResource->getRealPath(), 0, $resourceStrLength) == $resource) {
                return true;
            }
        }

        return false;
    }
}
