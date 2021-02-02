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

use Hyperf\Di\Annotation\Inject;
use Hyperf\Tracer\SpanStarter;
use Hyperf\Tracer\TracerFactory;
use Hyperf\Utils\Context;
use Hyperf\Utils\Coroutine;
use Hyperf\Contract\ContainerInterface;

use OpenTracing\Span;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TraceMiddleware implements MiddlewareInterface
{
    use SpanStarter;

    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Process an incoming server request.
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $tracer = make(TracerFactory::class)($this->container);

        Context::set('tracer', $tracer);

        $span = $this->buildSpan($request);

        try {
            $response = $handler->handle($request);
        } finally {
            $span->finish();

            co(function () use ($tracer) {
                $tracer->flush();
            });
        }

        return $response;
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
