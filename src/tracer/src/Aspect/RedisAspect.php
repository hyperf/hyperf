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

use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Redis\Redis;
use Hyperf\Tracer\SpanStarter;
use Hyperf\Tracer\SpanTagManager;
use Hyperf\Tracer\SwitchManager;
use Throwable;

class RedisAspect extends AbstractAspect
{
    use SpanStarter;

    public array $classes = [
        Redis::class . '::__call',
    ];

    public function __construct(private SwitchManager $switchManager, private SpanTagManager $spanTagManager)
    {
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
        $span = $this->startSpan('Redis::' . $arguments['name']);
        $span->setTag($this->spanTagManager->get('redis', 'arguments'), json_encode($arguments['arguments']));
        try {
            $result = $proceedingJoinPoint->process();
            $span->setTag($this->spanTagManager->get('redis', 'result'), json_encode($result));
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
