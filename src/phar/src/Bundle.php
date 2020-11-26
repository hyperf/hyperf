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
     * resource list
     * @param  array
     */
    private $resource_list = [];

    /**
     *  Add a file to the resource bundle
     * @param string $file
     * @return Bundle
     */
    public function addFile(string $file)
    {
        $this->resource_list[] = $file;
        return $this;
    }

    /**
     * Add a directory package to a resource package
     *
     * @return Bundle
     */
    public function addDir(Finder $dir)
    {
        $this->resource_list[] = $dir;
        return $this;
    }

    /**
     * Determines whether the file exists in the resource bundle
     * @param string $resource
     * @return bool
     */
    public function checkContains(string $resource)
    {
        foreach ($this->resource_list as $containedResource) {
            if ($containedResource instanceof Finder && $this->directoryContains($containedResource, $resource)) {
                return true;
            }
            if (is_string($containedResource) && $containedResource == $resource) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determines whether the file exists in the folder resource bundle
     * @param Finder $dir
     * @param string $resource
     * @return bool
     */
    private function directoryContains(Finder $dir, string $resource)
    {
        foreach ($dir as $containedResource) {
            /* @var $containedResource SplFileInfo */
            if (substr($containedResource->getRealPath(), 0, strlen($resource)) == $resource) {
                return true;
            }
        }

        return false;
    }


    /**
     * Returns an iterator for a list of resources
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->resource_list);
    }

}
