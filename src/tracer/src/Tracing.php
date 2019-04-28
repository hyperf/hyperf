<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Tracer;

use Hyperf\Utils\Context;
use Hyperf\Utils\Traits\CoroutineProxy;
use Psr\Http\Message\ServerRequestInterface;
use Zipkin\Propagation\Map;
use Zipkin\Span;
use Zipkin\TracingBuilder;
use const Zipkin\Kind\SERVER;

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

    public function span(string $name, string $kind = SERVER)
    {
        $root = Context::get('tracer.root');
        if (! $root instanceof Span) {
            /** @var ServerRequestInterface $request */
            $request = Context::get(ServerRequestInterface::class);
            if (! $request instanceof ServerRequestInterface) {
                throw new \RuntimeException('ServerRequest object missing.');
            }
            $carrier = array_map(function ($header) {
                return $header[0];
            }, $request->getHeaders());
            // Extracts the context from the HTTP headers.
            $extractor = $this->getPropagation()->getExtractor(new Map());
            $extractedContext = $extractor($carrier);
            $root = $this->getTracer()->nextSpan($extractedContext);
            $root->setName($name);
            $root->setKind($kind);
            Context::set('tracer.root', $root);
            return $root;
        }
        $child = $this->getTracer()->newChild($root->getContext());
        $child->setName($name);
        $child->setKind($kind);
        return $child;
    }

    /**
     * All tracing commands start with a {@link Span}. Use a tracer to create spans.
     *
     * @return \Zipkin\Tracer
     */
    public function getTracer()
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * When a trace leaves the process, it needs to be propagated, usually via headers. This utility
     * is used to inject or extract a trace context from remote requests.
     *
     * @return \Zipkin\Propagation\Propagation
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
