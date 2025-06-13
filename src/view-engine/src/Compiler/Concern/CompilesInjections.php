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

trait CompilesInjections
{
    /**
     * Compile the inject statements into valid PHP.
     */
    protected function compileInject(string $expression): string
    {
        $segments = explode(',', preg_replace('/[\(\)]/', '', $expression));

        $variable = trim($segments[0], " '\"");

        $service = trim($segments[1]);

        return "<?php \${$variable} = \\Hyperf\\ViewEngine\\T::inject({$service}); ?>";
    }
}
