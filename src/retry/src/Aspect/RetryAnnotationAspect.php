<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Retry\Aspect;

/**
 * @Aspect
 */
class RetryAnnotationAspect implements AroundInterface
{
    public $classes = [];

    public $annotations = [
        Retry::class,
    ];

    private function in(\Throwable $t, array $arr) : bool
    {
        return Arr::first(
            $arr,
            function ($v) {
                return $t instanceof $v;
            }
        ) ? true : false;
    }

    private function isRetriable(\Throwable $t) : bool
    {
        if ($this->in($t, $this->annotation->ignoreThrowables)) {
            return false;
        }

        if ($this->in($t, $this->annotation->retryThrowables)) {
            return true;
        }

        if (call_user_func($this->annotation->retryOnThrowablePredicate, $t)) {
            return true;
        }

        return false;
    }

    /**
     * @throws RateLimitException limit but without handle
     * @throws StorageException when the storage driver bootstrap failed
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $this->annotation = $this->getAnnotations($proceedingJoinPoint);
        $attempts = 0;
        $strategy = make(
            $this->annotation->strategy,
            ['base' => $this->annotation->base]
        );

        begin:

        try {
            $res = $proceedingJoinPoint->process();
        } catch (Throwable $t) {
            if ($attempts >= $this->annotation->maxAttempts) {
                throw $t;
            }
            if (!$this->isRetriable($t)) {
                throw $t;
            }
            ++$attempts;
            $strategy->sleep();
            goto begin;
        }
        

        if ($attempts >= $this->annotation->maxAttempts) {
            return $res;
        }

        $shouldRetry = call_user_func($this->annotation->retryOnResultPredicate, $res);

        if ($shouldRetry) {
            ++$attempts;
            $strategy->sleep();
            goto begin;
        }

        return $res;
    }

    public function getAnnotations(ProceedingJoinPoint $proceedingJoinPoint): array
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        return [
            $metadata->class[Retry::class] ?? null,
            $metadata->method[Retry::class] ?? null,
        ];
    }
}
