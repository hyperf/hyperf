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

namespace Hyperf\Tracer\Middleware;

use Hyperf\Utils\Coroutine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use const Zipkin\Kind\SERVER;
use Zipkin\Propagation\Map;
use Zipkin\Tracing;

class TraceMiddeware implements MiddlewareInterface
{
    /**
     * @var Tracing
     */
    private $tracing;

    public function __construct(Tracing $tracing)
    {
        $this->tracing = $tracing;
    }

    /**
     * Process an incoming server request.
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $span = $this->buildSpan($request);
        $span->start();

        $response = $handler->handle($request);

        $span->finish();

        $tracer = $this->tracing->getTracer();
        defer(function () use ($tracer) {
            $tracer->flush();
        });

        return $response;
    }

    protected function buildSpan(ServerRequestInterface $request)
    {
        $uri = $request->getUri();
        $carrier = array_map(function ($header) {
            return $header[0];
        }, $request->getHeaders());
        // Extracts the context from the HTTP headers.
        $extractor = $this->tracing->getPropagation()->getExtractor(new Map());
        $extractedContext = $extractor($carrier);
        $span = $this->tracing->getTracer()->nextSpan($extractedContext);
        $span->setKind(SERVER);
        $span->setName('Request');
        $span->tag('coroutine.id', Coroutine::id());
        $span->tag('request.path',$path = (string) $uri);
        $span->tag('request.method',$path = $request->getMethod());
        foreach ($request->getHeaders() as $key => $value) {
            $span->tag('request.header.' . $key, implode(', ', $value));
        }

        return $span;
    }
}
