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

namespace Hyperf\ViewEngine\Compiler\Concern;

trait CompilesRawPhp
{
    /**
     * Compile the raw PHP statements into valid PHP.
     */
    protected function compilePhp(?string $expression): string
    {
        if ($expression) {
            return "<?php {$expression}; ?>";
        }

        return '@php';
    }

    /**
     * Compile the unset statements into valid PHP.
     */
    protected function compileUnset(string $expression): string
    {
        return "<?php unset{$expression}; ?>";
    }
}
