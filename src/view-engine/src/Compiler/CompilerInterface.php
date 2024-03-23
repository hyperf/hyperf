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

interface CompilerInterface
{
    /**
     * Get the path to the compiled version of a view.
     */
    public function getCompiledPath(string $path): string;

    /**
     * Determine if the given view is expired.
     */
    public function isExpired(string $path): bool;

    /**
     * Compile the view at the given path.
     */
    public function compile(?string $path);
}
