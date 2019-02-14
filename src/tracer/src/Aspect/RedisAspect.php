<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Tracer\Aspect;

use Hyperf\Redis\Redis;
use Hyperf\Tracer\Tracing;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Tracer\SwitchManager;
use Hyperf\Di\Aop\ArroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;

/**
 * @Aspect
 */
class RedisAspect implements ArroundInterface
{
    /**
     * @var array
     */
    public $classes
        = [
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

    /**
     * @var SwitchManager
     */
    private $switchManager;

    public function __construct(Tracing $tracing, SwitchManager $switchManager)
    {
        $this->tracing = $tracing;
        $this->switchManager = $switchManager;
    }

    /**
     * @return mixed return the value from process method of ProceedingJoinPoint, or the value that you handled
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if ($this->switchManager->isEnable('redis') === false) {
            return $proceedingJoinPoint->process();
        }
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
