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

trait CompilesTranslations
{
    /**
     * Compile the lang statements into valid PHP.
     */
    protected function compileLang(?string $expression): string
    {
        if (is_null($expression)) {
            return '<?php $__env->startTranslation(); ?>';
        }
        if ($expression[1] === '[') {
            return "<?php \$__env->startTranslation{$expression}; ?>";
        }

        return "<?php echo \\Hyperf\\ViewEngine\\T::translator()->get{$expression}; ?>";
    }

    /**
     * Compile the end-lang statements into valid PHP.
     */
    protected function compileEndlang(): string
    {
        return '<?php echo $__env->renderTranslation(); ?>';
    }

    /**
     * Compile the choice statements into valid PHP.
     */
    protected function compileChoice(string $expression): string
    {
        return "<?php echo \\Hyperf\\ViewEngine\\T::translator()->choice{$expression}; ?>";
    }
}
