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
    protected string $directory;

    public function __construct(protected array $package, string $directory)
    {
        $this->directory = rtrim($directory, '/') . '/';
    }

    /**
     * Get full package name.
     */
    public function getName(): ?string
    {
        return $this->package['name'] ?? null;
    }

    /**
     * Gets the short package name
     * If not, the pathname is used as the package name.
     */
    public function getShortName(): string
    {
        $name = $this->getName();
        if ($name === null) {
            $name = realpath($this->getDirectory());
            if ($name === false) {
                $name = $this->getDirectory();
            }
        }
        return basename($name);
    }

    /**
     * Gets the relative address of the vendor directory, which supports custom addresses in composer.json.
     */
    public function getVendorPath(): string
    {
        $vendor = 'vendor';
        if (isset($this->package['config']['vendor-dir'])) {
            $vendor = $this->package['config']['vendor-dir'];
        }
        return $vendor . '/';
    }

    /**
     * Gets the absolute address of the vendor directory.
     */
    public function getVendorAbsolutePath(): string
    {
        return $this->getDirectory() . $this->getVendorPath();
    }

    /**
     * Get package directory.
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * Get resource bundle object.
     */
    public function bundle(?Finder $finder = null): Bundle
    {
        $bundle = new Bundle();
        $dir = $this->getDirectory();
        $vendorPath = $this->getVendorPath();
        if (empty($this->package['autoload']) && ! is_dir($dir . $vendorPath)) {
            return $bundle;
        }
        if ($finder == null) {
            $finder = Finder::create()
                ->files()
                ->ignoreVCS(true)
                ->exclude(rtrim($vendorPath, '/'))
                ->notPath('/^composer\.phar/')
                ->in($dir);
        }
        return $bundle->addFinder($finder);
    }

    /**
     * Gets the executable file path, and the directory address where the Phar package will run.
     */
    public function getBins(): array
    {
        return $this->package['bin'] ?? [];
    }
}
