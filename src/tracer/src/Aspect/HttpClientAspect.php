<?php

namespace Hyperf\Tracer\Aspect;


use GuzzleHttp\Client;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\ArroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Tracer\Tracing;
use Zipkin\Propagation\Map;

/**
 * @Aspect()
 */
class HttpClientAspect implements ArroundInterface
{

    public $classes = [
        Client::class . '::requestAsync'
    ];

    public $annotations = [];

    /**
     * @var Tracing
     */
    private $tracing;

    public function __construct(Tracing $tracing)
    {
        $this->tracing = $tracing;
    }

    /**
     * @return mixed return the value from process method of ProceedingJoinPoint, or the value that you handled
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $options = $proceedingJoinPoint->arguments['keys']['options'];
        if (isset($options['no_aspect']) && $options['no_aspect'] === true) {
            return $proceedingJoinPoint->process();
        }
        $span = $this->tracing->span('guzzlehttp.request', \Zipkin\Kind\CLIENT);
        $span->tag('source', $proceedingJoinPoint->className . '::' . $proceedingJoinPoint->methodName);
        $appendHeaders = [];
        /* Injects the context into the wire */
        $injector = $this->tracing->getPropagation()->getInjector(new Map());
        $injector($span->getContext(), $appendHeaders);
        $options['headers'] = array_replace($options['headers'] ?? [], $appendHeaders);
        $proceedingJoinPoint->arguments['keys']['options'] = $options;
        $span->start();
        $result = $proceedingJoinPoint->process();
        $span->finish();
        return $result;
    }
}