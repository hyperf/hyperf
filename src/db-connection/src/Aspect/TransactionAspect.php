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
namespace Hyperf\DbConnection\Aspect;

use Hyperf\DbConnection\Annotation\Transactional;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Annotation\MultipleAnnotation;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\Di\Exception\AnnotationException;

/**
 * @Aspect
 */
class TransactionAspect extends AbstractAspect
{
    public $annotations = [
        Transactional::class,
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $annotations = $this->getTransactionalAnnotations($proceedingJoinPoint->className, $proceedingJoinPoint->methodName);

        $func = static function () use ($proceedingJoinPoint) {
            return $proceedingJoinPoint->process();
        };
        $annotations = array_reverse($annotations->toAnnotations());
        foreach ($annotations as $transactional) {
            $func = static function () use ($transactional, $func) {
                return Db::connection($transactional->connection)->transaction(
                    $func,
                    $transactional->attempts
                );
            };
        }
        return $func();
    }

    protected function getTransactionalAnnotations(string $className, string $method): MultipleAnnotation
    {
        $annotations = AnnotationCollector::getClassMethodAnnotation($className, $method)[Transactional::class] ?? null;
        foreach ($annotations as $annotation) {
            if (! $annotation instanceof Transactional) {
                throw new AnnotationException("Annotation Transactional couldn't be collected successfully.");
            }
        }
        return $annotations;
    }
}
