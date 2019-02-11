<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Tracer;

use Hyperf\Utils\Context;
use Hyperf\Utils\Traits\CoroutineProxy;
use Zipkin\TracingBuilder;

class Tracing implements \Zipkin\Tracing
{
    use CoroutineProxy;

    protected $proxyKey = \Zipkin\Tracing::class;

    /**
     * @var TracingBuilder
     */
    protected $tracingBuilder;

    public function __construct(TracingBuilder $tracingBuilder)
    {
        $this->tracingBuilder = $tracingBuilder;
    }

    /**
     * All tracing commands start with a {@link Span}. Use a tracer to create spans.
     *
     * @return Tracer
     */
    public function getTracer()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * When a trace leaves the process, it needs to be propagated, usually via headers. This utility
     * is used to inject or extract a trace context from remote requests.
     *
     * @return Propagation
     */
    public function getPropagation()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * When true, no recording is done and nothing is reported to zipkin. However, trace context is
     * still injected into outgoing requests.
     *
     * @return bool
     * @see Span#isNoop()
     */
    public function isNoop()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    protected function getTargetObject(): \Zipkin\Tracing
    {
        $tracing = Context::get($this->proxyKey);
        if (! $tracing instanceof \Zipkin\Tracing) {
            Context::set($this->proxyKey, $tracing = $this->tracingBuilder->build());
        }
        return $tracing;
    }
}
