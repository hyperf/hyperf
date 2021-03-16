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
        $transactionalAnnotation = $this->getTransactionalAnnotation($proceedingJoinPoint->className, $proceedingJoinPoint->methodName);

        if ($transactionalAnnotation) {
            $connection = $transactionalAnnotation->connection;
            return Db::connection($connection)->transaction(
                function () use ($proceedingJoinPoint) {
                    return $proceedingJoinPoint->process();
                }
            );
        }
        return Db::transaction(
            function () use ($proceedingJoinPoint) {
                return $proceedingJoinPoint->process();
            }
        );
    }

    protected function getTransactionalAnnotation(string $className, string $method): ?Transactional
    {
        $collector = AnnotationCollector::get("${className}._m.${method}");
        $res = $collector[Transactional::class] ?? null;
        if ($res instanceof Transactional) {
            return $res;
        }
        return null;
    }
}
