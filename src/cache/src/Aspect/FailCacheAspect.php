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
namespace Hyperf\Cache\Aspect;

use Hyperf\Cache\Annotation\FailCache;
use Hyperf\Cache\AnnotationManager;
use Hyperf\Cache\CacheManager;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Throwable;

class FailCacheAspect extends AbstractAspect
{
    public array $classes = [];

    public array $annotations = [
        FailCache::class,
    ];

    public function __construct(protected CacheManager $manager, protected AnnotationManager $annotationManager, private StdoutLoggerInterface $logger)
    {
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $className = $proceedingJoinPoint->className;
        $method = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments['keys'];

        [$key, $ttl, $group] = $this->annotationManager->getFailCacheValue($className, $method, $arguments);

        $driver = $this->manager->getDriver($group);

        try {
            $result = $proceedingJoinPoint->process();
            $driver->set($key, $result, $ttl);
        } catch (Throwable $throwable) {
            [$has, $result] = $driver->fetch($key);
            if (! $has) {
                throw $throwable;
            }
            $this->logger->debug(sprintf('Returns fail cache [%s]', $key));
        }

        return $result;
    }
}
