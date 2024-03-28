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

use Hyperf\ViewEngine\Factory as ViewFactory;

trait CompilesLayouts
{
    /**
     * The name of the last section that was started.
     */
    protected ?string $lastSection = null;

    /**
     * Compile the extends statements into valid PHP.
     */
    protected function compileExtends(string $expression): string
    {
        $expression = $this->stripParentheses($expression);

        $echo = "<?php echo \$__env->make({$expression}, \\Hyperf\\Collection\\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";

        $this->footer[] = $echo;

        return '';
    }

    /**
     * Compile the section statements into valid PHP.
     */
    protected function compileSection(string $expression): string
    {
        $this->lastSection = trim($expression, "()'\" ");

        return "<?php \$__env->startSection{$expression}; ?>";
    }

    /**
     * Replace the `@parent` directive to a placeholder.
     */
    protected function compileParent(): string
    {
        return ViewFactory::parentPlaceholder($this->lastSection ?: '');
    }

    /**
     * Compile the yield statements into valid PHP.
     */
    protected function compileYield(string $expression): string
    {
        return "<?php echo \$__env->yieldContent{$expression}; ?>";
    }

    /**
     * Compile the show statements into valid PHP.
     */
    protected function compileShow(): string
    {
        return '<?php echo $__env->yieldSection(); ?>';
    }

    /**
     * Compile the append statements into valid PHP.
     */
    protected function compileAppend(): string
    {
        return '<?php $__env->appendSection(); ?>';
    }

    /**
     * Compile the overwrite statements into valid PHP.
     */
    protected function compileOverwrite(): string
    {
        return '<?php $__env->stopSection(true); ?>';
    }

    /**
     * Compile the stop statements into valid PHP.
     */
    protected function compileStop(): string
    {
        return '<?php $__env->stopSection(); ?>';
    }

    /**
     * Compile the end-section statements into valid PHP.
     */
    protected function compileEndsection(): string
    {
        return '<?php $__env->stopSection(); ?>';
    }
}
