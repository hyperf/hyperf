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

namespace Hyperf\Tracer\Adapter;

use Hyperf\Tracer\Contract\NamedFactoryInterface;
use OpenTracing\NoopTracer;
use OpenTracing\Tracer;

class NoOpTracerFactory implements NamedFactoryInterface
{
    public function make(string $name): Tracer
    {
        return new NoopTracer();
    }
}
