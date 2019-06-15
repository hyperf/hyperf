<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Tracer\Aspect;

use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Tracer\SwitchManager;
use Hyperf\Tracer\Tracing;

/**
 * Aspect.
 */
class MethodAspect extends AbstractAspect
{
    /**
     * @var array
     */
    public $classes = [
        'App*',
    ];

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
        if ($this->switchManager->isEnable('method') === false) {
            return $proceedingJoinPoint->process();
        }

        $key = $proceedingJoinPoint->className . '::' . $proceedingJoinPoint->methodName;
        $span = $this->tracing->span($key);
        $span->start();
        $result = $proceedingJoinPoint->process();
        $span->finish();
        return $result;
    }
}
