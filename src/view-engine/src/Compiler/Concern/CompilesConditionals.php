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

use Hyperf\Contract\ConfigInterface;

trait CompilesConditionals
{
    /**
     * Identifier for the first case in switch statement.
     */
    protected bool $firstCaseInSwitch = true;

    /**
     * Compile an end-once block into valid PHP.
     */
    public function compileEndOnce(): string
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the if-auth statements into valid PHP.
     */
    protected function compileAuth(?string $guard = null): string
    {
        $guard = is_null($guard) ? '()' : $guard;

        return "<?php if(auth()->guard{$guard}->check()): ?>";
    }

    /**
     * Compile the else-auth statements into valid PHP.
     */
    protected function compileElseAuth(?string $guard = null): string
    {
        $guard = is_null($guard) ? '()' : $guard;

        return "<?php elseif(auth()->guard{$guard}->check()): ?>";
    }

    /**
     * Compile the end-auth statements into valid PHP.
     */
    protected function compileEndAuth(): string
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the `@env` statements into valid PHP.
     */
    protected function compileEnv(string $environments): string
    {
        $config = ConfigInterface::class;
        $environments = trim($environments, '()[]');
        return "<?php if(\\in_array(\$__env->getContainer()->get({$config}::class)->get('app_env'), [{$environments}])): ?>";
    }

    /**
     * Compile the end-env statements into valid PHP.
     */
    protected function compileEndEnv(): string
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the `@production` statements into valid PHP.
     */
    protected function compileProduction(): string
    {
        return $this->compileEnv("'prod', 'production'");
    }

    /**
     * Compile the end-production statements into valid PHP.
     */
    protected function compileEndProduction(): string
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the if-guest statements into valid PHP.
     */
    protected function compileGuest(?string $guard = null): string
    {
        $guard = is_null($guard) ? '()' : $guard;

        return "<?php if(auth()->guard{$guard}->guest()): ?>";
    }

    /**
     * Compile the else-guest statements into valid PHP.
     */
    protected function compileElseGuest(?string $guard = null): string
    {
        $guard = is_null($guard) ? '()' : $guard;

        return "<?php elseif(auth()->guard{$guard}->guest()): ?>";
    }

    /**
     * Compile the end-guest statements into valid PHP.
     */
    protected function compileEndGuest(): string
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the has-section statements into valid PHP.
     */
    protected function compileHasSection(string $expression): string
    {
        return "<?php if (! empty(trim(\$__env->yieldContent{$expression}))): ?>";
    }

    /**
     * Compile the section-missing statements into valid PHP.
     */
    protected function compileSectionMissing(string $expression): string
    {
        return "<?php if (empty(trim(\$__env->yieldContent{$expression}))): ?>";
    }

    /**
     * Compile the if statements into valid PHP.
     */
    protected function compileIf(string $expression): string
    {
        return "<?php if{$expression}: ?>";
    }

    /**
     * Compile the unless statements into valid PHP.
     */
    protected function compileUnless(string $expression): string
    {
        return "<?php if (! {$expression}): ?>";
    }

    /**
     * Compile the else-if statements into valid PHP.
     */
    protected function compileElseif(string $expression): string
    {
        return "<?php elseif{$expression}: ?>";
    }

    /**
     * Compile the else statements into valid PHP.
     */
    protected function compileElse(): string
    {
        return '<?php else: ?>';
    }

    /**
     * Compile the end-if statements into valid PHP.
     */
    protected function compileEndif(): string
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the end-unless statements into valid PHP.
     */
    protected function compileEndunless(): string
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the if-isset statements into valid PHP.
     */
    protected function compileIsset(string $expression): string
    {
        return "<?php if(isset{$expression}): ?>";
    }

    /**
     * Compile the end-isset statements into valid PHP.
     */
    protected function compileEndIsset(): string
    {
        return '<?php endif; ?>';
    }

    /**
     * Compile the switch statements into valid PHP.
     */
    protected function compileSwitch(string $expression): string
    {
        $this->firstCaseInSwitch = true;

        return "<?php switch{$expression}:";
    }

    /**
     * Compile the case statements into valid PHP.
     */
    protected function compileCase(string $expression): string
    {
        if ($this->firstCaseInSwitch) {
            $this->firstCaseInSwitch = false;

            return "case {$expression}: ?>";
        }

        return "<?php case {$expression}: ?>";
    }

    /**
     * Compile the default statements in switch case into valid PHP.
     */
    protected function compileDefault(): string
    {
        return '<?php default: ?>';
    }

    /**
     * Compile the end switch statements into valid PHP.
     */
    protected function compileEndSwitch(): string
    {
        return '<?php endswitch; ?>';
    }

    /**
     * Compile an once block into valid PHP.
     */
    protected function compileOnce(?string $id = null): string
    {
        $id = $id ? $this->stripParentheses($id) : "'" . md5((string) microtime(true)) . "'";

        return '<?php if (! $__env->hasRenderedOnce(' . $id . ')): $__env->markAsRenderedOnce(' . $id . '); ?>';
    }
}
