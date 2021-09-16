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

use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\Tracer\SpanStarter;
use Hyperf\Tracer\SwitchManager;
use Hyperf\Utils\Coroutine;
use OpenTracing\Span;
use OpenTracing\Tracer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TraceMiddleware implements MiddlewareInterface
{
    use SpanStarter;

    /**
     * @var SwitchManager
     */
    protected $switchManager;

    /**
     * @var Tracer
     */
    private $tracer;

    public function __construct(Tracer $tracer, SwitchManager $switchManager)
    {
        $this->tracer = $tracer;
        $this->switchManager = $switchManager;
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

        defer(function () {
            try {
                $this->tracer->flush();
            } catch (\Throwable $exception) {
            }
        });
        try {
            $response = $handler->handle($request);
            $span->setTag('response.statusCode', $response->getStatusCode());
        } catch (\Throwable $exception) {
            $this->switchManager->isEnable('error') && $this->appendExceptionToSpan($span, $exception);
            throw $exception;
        } finally {
            $span->finish();
        }

        return $response;
    }

    protected function appendExceptionToSpan(Span $span, \Throwable $exception): void
    {
        $span->setTag('error', true);
        $span->setTag('error.code', $exception->getCode());
        $span->setTag('error.file', $exception->getFile());
        $span->setTag('error.line', $exception->getLine());
        $span->setTag('error.message', $exception->getMessage());
        $span->setTag('error.stackTrace', $exception->getTraceAsString());
        $span->setTag('error.type', get_class($exception));

        if ($exception instanceof HttpException) {
            $span->setTag('error.statusCode', $exception->getStatusCode());
        }
    }

    protected function buildSpan(ServerRequestInterface $request): Span
    {
        $uri = $request->getUri();
        $span = $this->startSpan('request');
        $span->setTag('coroutine.id', (string) Coroutine::id());
        $span->setTag('request.path', (string) $uri);
        $span->setTag('request.method', $request->getMethod());
        foreach ($request->getHeaders() as $key => $value) {
            $span->setTag('request.header.' . $key, implode(', ', $value));
        }
        return $span;
    }
}
