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
use Hyperf\HttpServer\Event\RequestReceived;
use Hyperf\HttpServer\Event\RequestTerminated;
use Hyperf\Tracer\SpanStarter;
use Hyperf\Tracer\SpanTagManager;
use Hyperf\Tracer\SwitchManager;
use Hyperf\Tracer\TracerContext;
use OpenTracing\Span;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

class TraceListener implements ListenerInterface
{
    use SpanStarter;

    protected ?string $server = null;

    public function __construct(private SwitchManager $switchManager, private SpanTagManager $spanTagManager)
    {
    }

    public function listen(): array
    {
        return [
            RequestReceived::class,
            RequestTerminated::class,
        ];
    }

    /**
     * @param RequestReceived|RequestTerminated $event
     */
    public function process(object $event): void
    {
        if ($this->server && $event->server != $this->server) {
            return;
        }

        if ($event::class == RequestReceived::class) {
            $this->buildSpan($event->request);
            return;
        }

        $tracer = TracerContext::getTracer();
        $span = TracerContext::getRoot();
        $response = $event->response;
        $exception = $event->exception;
        $span->setTag($this->spanTagManager->get('response', 'status_code'), $response->getStatusCode());

        if ($exception && $this->switchManager->isEnable('exception')) {
            $this->appendExceptionToSpan($span, $exception);

            if ($exception instanceof HttpException) {
                $span->setTag($this->spanTagManager->get('response', 'status_code'), $exception->getStatusCode());
            }
        }

        $span->finish();
        $tracer->flush();
    }

    protected function appendExceptionToSpan(Span $span, Throwable $exception): void
    {
        $span->setTag('error', true);
        $span->setTag($this->spanTagManager->get('exception', 'class'), get_class($exception));
        $span->setTag($this->spanTagManager->get('exception', 'code'), $exception->getCode());
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
        foreach ($request->getHeaders() as $key => $value) {
            $span->setTag($this->spanTagManager->get('request', 'header') . '.' . $key, implode(', ', $value));
        }
        return $span;
    }
}
