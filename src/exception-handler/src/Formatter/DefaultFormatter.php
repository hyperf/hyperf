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

namespace Hyperf\ExceptionHandler\Formatter;

use Throwable;

class DefaultFormatter implements FormatterInterface
{
    public function format(Throwable $throwable): string
    {
        $lineMapFixer = 'Hyperf\Di\Aop\LineMapFixer';
        if (class_exists($lineMapFixer)) {
            return $lineMapFixer::format($throwable);
        }
        return (string) $throwable;
    }
}
