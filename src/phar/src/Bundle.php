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
     * 资源列表.
     *
     * @param  array
     */
    private $resource_list = [];

    /**
     * 添加文件到资源包.
     *
     * @return Bundle
     */
    public function addFile(string $file)
    {
        $this->resource_list[] = $file;
        return $this;
    }

    /**
     * 添加目录包到资源包.
     *
     * @return Bundle
     */
    public function addDir(Finder $dir)
    {
        $this->resource_list[] = $dir;
        return $this;
    }

    /**
     * 判断文件是否存在在资源包中.
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
     * 返回资源列表的迭代器.
     * @return ArrayIterator|Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->resource_list);
    }

    /**
     * 判断文件是否存在文件夹资源包中.
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
}
