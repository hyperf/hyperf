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

namespace Hyperf\Tracer\Aspect;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Tracer\SpanStarter;
use Hyperf\Tracer\SpanTagManager;
use Hyperf\Tracer\SwitchManager;
use Hyperf\Tracer\TracerContext;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

use function is_callable;

use const OpenTracing\Formats\TEXT_MAP;

class HttpClientAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        Client::class . '::transfer',
    ];

    public function __construct(private SwitchManager $switchManager, private SpanTagManager $spanTagManager)
    {
    }

    /**
     * @return mixed return the value from process method of ProceedingJoinPoint, or the value that you handled
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if ($this->switchManager->isEnable('guzzle') === false) {
            return $proceedingJoinPoint->process();
        }

        $options = $proceedingJoinPoint->arguments['keys']['options'];

        if (($options['no_aspect'] ?? false) === true) {
            return $proceedingJoinPoint->process();
        }

        $arguments = $proceedingJoinPoint->arguments;
        /** @var RequestInterface $request */
        $request = $arguments['keys']['request'];
        $method = $request->getMethod();
        $uri = $request->getUri()->__toString();
        $key = "HTTP Request [{$method}] {$uri}";

        // Start a new span
        $span = $this->startSpan($key);
        $span->setTag('source', $proceedingJoinPoint->className . '::' . $proceedingJoinPoint->methodName);
        if ($this->spanTagManager->has('http_client', 'http.url')) {
            $span->setTag($this->spanTagManager->get('http_client', 'http.url'), $uri);
        }
        if ($this->spanTagManager->has('http_client', 'http.method')) {
            $span->setTag($this->spanTagManager->get('http_client', 'http.method'), $method);
        }

        // Injects the context into the wire
        $appendHeaders = [];
        TracerContext::getTracer()->inject(
            $span->getContext(),
            TEXT_MAP,
            $appendHeaders
        );
        $options['headers'] = array_replace($options['headers'] ?? [], $appendHeaders);

        // Inject the span into the options
        $onStats = $options['on_stats'] ?? null;
        $options['on_stats'] = function (TransferStats $stats) use ($span, $onStats) {
            $response = $stats->getResponse();

            if ($response instanceof ResponseInterface) {
                $span->setTag($this->spanTagManager->get('http_client', 'http.status_code'), (string) $response->getStatusCode());
            }

            if (
                ($e = $stats->getHandlerErrorData()) instanceof Throwable
                && $this->switchManager->isEnable('exception')
                && ! $this->switchManager->isIgnoreException($e)
            ) {
                $span->setTag('error', true);
                $span->log(['message', $e->getMessage(), 'code' => $e->getCode(), 'stacktrace' => $e->getTraceAsString()]);
            }

            $span->finish();

            if (is_callable($onStats)) {
                $onStats($stats);
            }
        };

        $proceedingJoinPoint->arguments['keys']['options'] = $options;

        return $proceedingJoinPoint->process();
    }
}
