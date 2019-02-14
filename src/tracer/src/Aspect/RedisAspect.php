<?php

namespace Hyperf\Tracer\Aspect;


use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\ArroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Redis\Redis;
use Hyperf\Tracer\Tracing;

/**
 * @Aspect()
 */
class RedisAspect implements ArroundInterface
{

    /**
     * @var array
     */
    public $classes = [
        Redis::class . '::__call',
    ];

    /**
     * @var array
     */
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
        $arguments = $proceedingJoinPoint->arguments['keys'];
        $span = $this->tracing->span('Redis' . '::' . $arguments['name']);
        $span->start();
        $span->tag('arguments', json_encode($arguments['arguments']));
        $result = $proceedingJoinPoint->process();
        $span->tag('result', json_encode($result));
        $span->finish();
        return $result;
    }
}