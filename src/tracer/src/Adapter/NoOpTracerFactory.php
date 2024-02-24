<?php

declare(strict_types=1);

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