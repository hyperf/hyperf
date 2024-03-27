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
        return (string) $throwable;
    }
}
