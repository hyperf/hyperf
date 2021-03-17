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
        $transactional = $this->getTransactionalAnnotation($proceedingJoinPoint->className, $proceedingJoinPoint->methodName);

        return Db::connection($transactional->connection)->transaction(
            static function () use ($proceedingJoinPoint) {
                return $proceedingJoinPoint->process();
            },
            $transactional->attempts
        );
    }

    protected function getTransactionalAnnotation(string $className, string $method): Transactional
    {
        $annotation = AnnotationCollector::getClassMethodAnnotation($className, $method)[Transactional::class] ?? null;
        if (! $annotation instanceof Transactional) {
            throw new AnnotationException("Annotation Transactional couldn't be collected successfully.");
        }

        return $annotation;
    }
}
