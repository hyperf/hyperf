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

namespace Hyperf\Tracer\Middleware;

use Hyperf\Coroutine\Coroutine;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\Tracer\SpanStarter;
use Hyperf\Tracer\SpanTagManager;
use Hyperf\Tracer\SwitchManager;
use Hyperf\Tracer\TracerContext;
use OpenTracing\Span;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function Hyperf\Coroutine\defer;

class TraceMiddleware implements MiddlewareInterface
{
    use SpanStarter;

    public function __construct(private SwitchManager $switchManager, private SpanTagManager $spanTagManager)
    {
    }

    /**
     * Process an incoming server request.
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $tracer = TracerContext::getTracer();
        $span = $this->buildSpan($request);

        defer(function () use ($tracer) {
            try {
                $tracer->flush();
            } catch (Throwable) {
            }
        });
        try {
            $response = $handler->handle($request);
            if ($traceId = TracerContext::getTraceId()) {
                $response = $response->withHeader('Trace-Id', $traceId);
            }
            $span->setTag($this->spanTagManager->get('response', 'status_code'), (string) $response->getStatusCode());
            if ($this->spanTagManager->has('response', 'body')) {
                $span->setTag($this->spanTagManager->get('response', 'body'), (string) $response->getBody());
            }
        } catch (Throwable $exception) {
            if ($this->switchManager->isEnable('exception') && ! $this->switchManager->isIgnoreException($exception)) {
                $this->appendExceptionToSpan($span, $exception);
            }
            if ($exception instanceof HttpException) {
                $span->setTag($this->spanTagManager->get('response', 'status_code'), (string) $exception->getStatusCode());
            }
            throw $exception;
        } finally {
            $span->finish();
        }

        return $response;
    }

    protected function appendExceptionToSpan(Span $span, Throwable $exception): void
    {
        $span->setTag('error', true);
        $span->setTag($this->spanTagManager->get('exception', 'class'), get_class($exception));
        $span->setTag($this->spanTagManager->get('exception', 'code'), (string) $exception->getCode());
        $span->setTag($this->spanTagManager->get('exception', 'message'), $exception->getMessage());
        $span->setTag($this->spanTagManager->get('exception', 'stack_trace'), (string) $exception);
    }

    protected function buildSpan(ServerRequestInterface $request): Span
    {
        $uri = $request->getUri();
        $span = $this->startSpan(sprintf('request: %s %s', $request->getMethod(), $uri->getPath()));
        $span->setTag($this->spanTagManager->get('coroutine', 'id'), (string) Coroutine::id());
        $span->setTag($this->spanTagManager->get('request', 'path'), (string) $uri->getPath());
        $span->setTag($this->spanTagManager->get('request', 'method'), $request->getMethod());
        $span->setTag($this->spanTagManager->get('request', 'uri'), (string) $uri);
        if ($this->spanTagManager->has('request', 'body')) {
            $span->setTag($this->spanTagManager->get('request', 'body'), (string) $request->getBody());
        }
        foreach ($request->getHeaders() as $key => $value) {
            $span->setTag($this->spanTagManager->get('request', 'header') . '.' . $key, implode(', ', $value));
        }
        return $span;
    }
}
