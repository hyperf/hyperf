<?php

namespace Hyperf\Tracer\Aspect;


use GuzzleHttp\Client;
use Hyperf\Di\Aop\ArroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Tracer\Tracing;
use Zipkin\Propagation\Map;

/**
 * @\Hyperf\Di\Annotation\Aspect()
 */
class HttpClientAspect implements ArroundInterface
{

    public $classes = [
        Client::class . '::prepareDefaults'
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
        $span = $this->tracing->span('guzzlehttp.request');
        $appendHeaders = [];
        /* Injects the context into the wire */
        $injector = $this->tracing->getPropagation()->getInjector(new Map());
        $injector($span->getContext(), $appendHeaders);
        $options['headers'] = array_replace($options['headers'] ?? [], $appendHeaders);
        $proceedingJoinPoint->arguments['keys']['options'] = $options;
        return $proceedingJoinPoint->process();
    }
}