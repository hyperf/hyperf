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
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Tracer\SpanStarter;
use Hyperf\Tracer\SpanTagManager;
use Hyperf\Tracer\SwitchManager;
use Hyperf\Tracer\TracerContext;
use Psr\Http\Message\ResponseInterface;
use Throwable;

use const OpenTracing\Formats\TEXT_MAP;

class HttpClientAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        Client::class . '::request',
        Client::class . '::requestAsync',
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
        if (isset($options['no_aspect']) && $options['no_aspect'] === true) {
            return $proceedingJoinPoint->process();
        }
        // Disable the aspect for the requestAsync method.
        if ($proceedingJoinPoint->methodName == 'request') {
            $options['no_aspect'] = true;
        }
        $arguments = $proceedingJoinPoint->arguments;
        $method = $arguments['keys']['method'] ?? 'Null';
        $uri = $arguments['keys']['uri'] ?? 'Null';
        $key = "HTTP Request [{$method}] {$uri}";
        $span = $this->startSpan($key);
        $span->setTag('source', $proceedingJoinPoint->className . '::' . $proceedingJoinPoint->methodName);
        if ($this->spanTagManager->has('http_client', 'http.url')) {
            $span->setTag($this->spanTagManager->get('http_client', 'http.url'), $uri);
        }
        if ($this->spanTagManager->has('http_client', 'http.method')) {
            $span->setTag($this->spanTagManager->get('http_client', 'http.method'), $method);
        }
        $appendHeaders = [];
        // Injects the context into the wire
        TracerContext::getTracer()->inject(
            $span->getContext(),
            TEXT_MAP,
            $appendHeaders
        );
        $options['headers'] = array_replace($options['headers'] ?? [], $appendHeaders);
        $proceedingJoinPoint->arguments['keys']['options'] = $options;

        try {
            $result = $proceedingJoinPoint->process();
            if ($result instanceof ResponseInterface) {
                $span->setTag($this->spanTagManager->get('http_client', 'http.status_code'), (string) $result->getStatusCode());
            }
        } catch (Throwable $e) {
            if ($this->switchManager->isEnable('exception') && ! $this->switchManager->isIgnoreException($e)) {
                $span->setTag('error', true);
                $span->log(['message', $e->getMessage(), 'code' => $e->getCode(), 'stacktrace' => $e->getTraceAsString()]);
            }
            throw $e;
        } finally {
            $span->finish();
        }
        return $result;
    }
}
