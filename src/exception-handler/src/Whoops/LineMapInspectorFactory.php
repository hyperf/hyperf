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

use Whoops\Inspector\InspectorFactoryInterface;
use Whoops\Inspector\InspectorInterface;

class LineMapInspectorFactory implements InspectorFactoryInterface
{
    public function create($exception): InspectorInterface
    {
        return new LineMapInspector($exception, $this);
    }
}
