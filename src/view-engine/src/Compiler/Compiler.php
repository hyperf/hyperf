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

namespace Hyperf\ViewEngine\Compiler;

use Hyperf\Support\Filesystem\Filesystem;
use InvalidArgumentException;

abstract class Compiler
{
    /**
     * Get the cache path for the compiled views.
     */
    protected ?string $cachePath = null;

    /**
     * Create a new compiler instance.
     *
     * @param Filesystem $files the Filesystem instance
     *
     * @throws InvalidArgumentException
     */
    public function __construct(protected Filesystem $files, string $cachePath)
    {
        if (! $cachePath) {
            throw new InvalidArgumentException('Please provide a valid cache path.');
        }

        if (! $this->files->exists($cachePath)) {
            $this->files->makeDirectory($cachePath);
        }

        $this->cachePath = $cachePath;
    }

    /**
     * Get the path to the compiled version of a view.
     */
    public function getCompiledPath(string $path): string
    {
        return $this->cachePath . '/' . sha1($path) . '.php';
    }

    /**
     * Determine if the view at the given path is expired.
     */
    public function isExpired(string $path): bool
    {
        $compiled = $this->getCompiledPath($path);

        // If the compiled file doesn't exist we will indicate that the view is expired
        // so that it can be re-compiled. Else, we will verify the last modification
        // of the views is less than the modification times of the compiled views.
        if (! $this->files->exists($compiled)) {
            return true;
        }

        return $this->files->lastModified($path) >=
            $this->files->lastModified($compiled);
    }
}
