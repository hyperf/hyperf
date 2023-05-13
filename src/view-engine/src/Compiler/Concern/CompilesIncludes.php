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

trait CompilesIncludes
{
    /**
     * Compile the each statements into valid PHP.
     */
    protected function compileEach(string $expression): string
    {
        return "<?php echo \$__env->renderEach{$expression}; ?>";
    }

    /**
     * Compile the include statements into valid PHP.
     */
    protected function compileInclude(string $expression): string
    {
        $expression = $this->stripParentheses($expression);

        return "<?php echo \$__env->make({$expression}, \\Hyperf\\Utils\\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
    }

    /**
     * Compile the include-if statements into valid PHP.
     */
    protected function compileIncludeIf(string $expression): string
    {
        $expression = $this->stripParentheses($expression);

        return "<?php if (\$__env->exists({$expression})) echo \$__env->make({$expression}, \\Hyperf\\Utils\\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
    }

    /**
     * Compile the include-when statements into valid PHP.
     */
    protected function compileIncludeWhen(string $expression): string
    {
        $expression = $this->stripParentheses($expression);

        return "<?php echo \$__env->renderWhen({$expression}, \\Hyperf\\Utils\\Arr::except(get_defined_vars(), ['__data', '__path'])); ?>";
    }

    /**
     * Compile the include-unless statements into valid PHP.
     */
    protected function compileIncludeUnless(string $expression): string
    {
        $expression = $this->stripParentheses($expression);

        return "<?php echo \$__env->renderUnless({$expression}, \\Hyperf\\Utils\\Arr::except(get_defined_vars(), ['__data', '__path'])); ?>";
    }

    /**
     * Compile the include-first statements into valid PHP.
     */
    protected function compileIncludeFirst(string $expression): string
    {
        $expression = $this->stripParentheses($expression);

        return "<?php echo \$__env->first({$expression}, \\Hyperf\\Utils\\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>";
    }
}
