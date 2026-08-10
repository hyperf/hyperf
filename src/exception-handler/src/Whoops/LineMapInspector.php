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

namespace Hyperf\ExceptionHandler\Whoops;

use Whoops\Exception\Inspector;

class LineMapInspector extends Inspector
{
    protected function getTrace($exception)
    {
        $trace = parent::getTrace($exception);
        foreach ($trace as &$frame) {
            $frame = $this->resolveFrame($frame);
        }
        return $trace;
    }

    protected function getFrameFromException($exception)
    {
        return $this->resolveFrame(parent::getFrameFromException($exception));
    }

    private function resolveFrame(array $frame): array
    {
        if (! isset($frame['file'], $frame['line'])) {
            return $frame;
        }

        $lineMapFixer = 'Hyperf\Di\Aop\LineMapFixer';
        if (class_exists($lineMapFixer)) {
            $location = $lineMapFixer::resolveLocation($frame['file'], $frame['line']);
            $frame['file'] = $location['file'];
            $frame['line'] = $location['line'];
        }
        return $frame;
    }
}
