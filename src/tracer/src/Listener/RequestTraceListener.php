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

namespace Hyperf\Tracer\Listener;

use Hyperf\Coroutine\Coroutine;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\HttpMessage\Exception\HttpException;
use Hyperf\HttpServer\Event\RequestHandled;
use Hyperf\HttpServer\Event\RequestReceived;
use Hyperf\HttpServer\Event\RequestTerminated;
use Hyperf\Tracer\SpanStarter;
use Hyperf\Tracer\SpanTagManager;
use Hyperf\Tracer\SwitchManager;
use Hyperf\Tracer\TracerContext;
use OpenTracing\Span;
use Psr\Http\Message\ServerRequestInterface;
use Swow\Psr7\Message\ResponsePlusInterface;
use Throwable;

class RequestTraceListener implements ListenerInterface
{
    use SpanStarter;

    public function __construct(private SwitchManager $switchManager, private SpanTagManager $spanTagManager)
    {
    }

    public function listen(): array
    {
        return [
            RequestReceived::class,
            RequestHandled::class,
            RequestTerminated::class,
        ];
    }

    public function process(object $event): void
    {
        match ($event::class) {
            RequestReceived::class => $this->handleRequestReceived($event),
            RequestHandled::class => $this->handleRequestHandled($event),
            RequestTerminated::class => $this->handleRequestTerminated($event),
            default => '', // fix phpstan error
        };
    }

    protected function handleRequestReceived(RequestReceived $event): void
    {
        $this->buildSpan($event->request);
    }

    protected function handleRequestHandled(RequestHandled $event): void
    {
        if ($event->response instanceof ResponsePlusInterface && $traceId = TracerContext::getTraceId()) {
            $event->response->addHeader('Trace-Id', $traceId);
        }
    }

    protected function handleRequestTerminated(RequestTerminated $event): void
    {
        $response = $event->response;

        if (! $response) {
            return;
        }

        $tracer = TracerContext::getTracer();
        $span = TracerContext::getRoot();
        $span->setTag($this->spanTagManager->get('response', 'status_code'), (string) $response->getStatusCode());
        if ($this->spanTagManager->has('response', 'body')) {
            $span->setTag($this->spanTagManager->get('response', 'body'), (string) $response->getBody());
        }

        if ($event->exception && $this->switchManager->isEnable('exception') && ! $this->switchManager->isIgnoreException($event->exception)) {
            $this->appendExceptionToSpan($span, $exception = $event->exception);

            if ($exception instanceof HttpException) {
                $span->setTag($this->spanTagManager->get('response', 'status_code'), $exception->getStatusCode());
                if ($this->spanTagManager->has('response', 'body')) {
                    $span->setTag($this->spanTagManager->get('response', 'body'), (string) $response->getBody());
                }
            }
        }

        $span->finish();
        $tracer->flush();
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
