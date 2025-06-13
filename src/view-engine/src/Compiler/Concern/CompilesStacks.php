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

trait CompilesStacks
{
    /**
     * Compile the stack statements into the content.
     */
    protected function compileStack(string $expression): string
    {
        return "<?php echo \$__env->yieldPushContent{$expression}; ?>";
    }

    /**
     * Compile the push statements into valid PHP.
     */
    protected function compilePush(string $expression): string
    {
        return "<?php \$__env->startPush{$expression}; ?>";
    }

    /**
     * Compile the end-push statements into valid PHP.
     */
    protected function compileEndpush(): string
    {
        return '<?php $__env->stopPush(); ?>';
    }

    /**
     * Compile the prepend statements into valid PHP.
     */
    protected function compilePrepend(string $expression): string
    {
        return "<?php \$__env->startPrepend{$expression}; ?>";
    }

    /**
     * Compile the end-prepend statements into valid PHP.
     */
    protected function compileEndprepend(): string
    {
        return '<?php $__env->stopPrepend(); ?>';
    }
}
