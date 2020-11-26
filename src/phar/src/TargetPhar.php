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

use Phar;
use Symfony\Component\Finder\Finder;
use Traversable;

class TargetPhar
{
    /**
     * @var Phar
     */
    private $phar;

    /**
     * @var HyperfPhar
     */
    private $hyperfPhar;

    public function __construct(Phar $phar, HyperfPhar $hyperfPhar)
    {
        $phar->startBuffering();
        $this->phar = $phar;
        $this->hyperfPhar = $hyperfPhar;
    }

    public function __toString(): string
    {
        $exploded = explode('/', $this->phar->getPath());
        return end($exploded);
    }

    /**
     * Start writing the Phar package.
     */
    public function stopBuffering()
    {
        $this->phar->stopBuffering();
    }

    /**
     * Add a resource bundle to the Phar package.
     */
    public function addBundle(Bundle $bundle)
    {
        /** @var Finder|string $resource */
        foreach ($bundle as $resource) {
            if (is_string($resource)) {
                $this->addFile($resource);
            } else {
                $this->buildFromIterator($resource);
            }
        }
    }

    /**
     * Add the file to the Phar package.
     * @param string $file the file name
     */
    public function addFile(string $file)
    {
        $this->phar->addFile($file, $this->hyperfPhar->getPathLocalToBase($file));
    }

    /**
     * Add folder resources to the Phar package.
     */
    public function buildFromIterator(Traversable $iterator)
    {
        $this->phar->buildFromIterator($iterator, $this->hyperfPhar->getPackage()->getDirectory());
    }

    /**
     * Create the default execution file.
     * @return string
     */
    public function createDefaultStub(string $indexFile, string $webIndexFile = null)
    {
        if($webIndexFile != null){
            return $this->phar->createDefaultStub($indexFile,$webIndexFile);
        }
        return $this->phar->createDefaultStub($indexFile);
    }

    /**
     * Set the default startup file.
     */
    public function setStub(string $stub)
    {
        $this->phar->setStub($stub);
    }

    /**
     * Add a string to the Phar package.
     * @param $local
     * @param $contents
     */
    public function addFromString($local, $contents)
    {
        $this->phar->addFromString($local, $contents);
    }
}
