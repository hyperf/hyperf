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

use Symfony\Component\Finder\Finder;

class Package
{
    private $package;

    private $directory;

    /**
     * Package constructor.
     * @param $directory
     */
    public function __construct(array $package, $directory)
    {
        $this->package = $package;
        $this->directory = rtrim($directory, '/') . '/';
    }

    /**
     * Get full package name.
     * @return null|mixed
     */
    public function getName()
    {
        return isset($this->package['name']) ? $this->package['name'] : null;
    }

    /**
     * Gets the short package name
     * If not, the pathname is used as the package name.
     * @return string
     */
    public function getShortName()
    {
        $name = $this->getName();
        if ($name === null) {
            $name = realpath($this->directory);
            if ($name === false) {
                $name = $this->directory;
            }
        }
        return basename($name);
    }

    /**
     * Gets the relative address of the vendor directory, which supports custom addresses in composer.json.
     * @return string
     */
    public function getPathVendor()
    {
        $vendor = 'vendor';
        if (isset($this->package['config']['vendor-dir'])) {
            $vendor = $this->package['config']['vendor-dir'];
        }
        return $vendor . '/';
    }

    /**
     * Get package directory.
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Get resource bundle object.
     * @return Bundle
     */
    public function bundle()
    {
        $bundle = new Bundle();
        if (empty($this->package['autoload']) && ! is_dir($this->directory . $this->getPathVendor())) {
            return $bundle;
        }
        $iterator = Finder::create()
            ->files()
            ->ignoreVCS(true)
            ->exclude(rtrim($this->getPathVendor(), '/'))
            ->notPath('/^composer\.phar/')
            ->in($this->getDirectory());

        return $bundle->addDir($iterator);
    }

    /**
     * Gets the executable file path, and the directory address where the Phar package will run.
     * @return array|mixed
     */
    public function getBins()
    {
        return isset($this->package['bin']) ? $this->package['bin'] : [];
    }
}
