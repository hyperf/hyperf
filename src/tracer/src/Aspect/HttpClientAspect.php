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
use OpenTracing\Tracer;
use Psr\Http\Message\ResponseInterface;
use Throwable;

use const OpenTracing\Formats\TEXT_MAP;

class HttpClientAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        Client::class . '::requestAsync',
    ];

    public function __construct(private Tracer $tracer, private SwitchManager $switchManager, private SpanTagManager $spanTagManager)
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
        $this->tracer->inject(
            $span->getContext(),
            TEXT_MAP,
            $appendHeaders
        );
        $options['headers'] = array_replace($options['headers'] ?? [], $appendHeaders);
        $proceedingJoinPoint->arguments['keys']['options'] = $options;

        try {
            $result = $proceedingJoinPoint->process();
            if ($result instanceof ResponseInterface) {
                $span->setTag($this->spanTagManager->get('http_client', 'http.status_code'), $result->getStatusCode());
            }
        } catch (Throwable $e) {
            $span->setTag('error', true);
            $span->log(['message', $e->getMessage(), 'code' => $e->getCode(), 'stacktrace' => $e->getTraceAsString()]);
            throw $e;
        } finally {
            $span->finish();
        }
        return $result;
    }
}
