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
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

/**
 * @Aspect
 */
class FailCacheAspect extends AbstractAspect
{
    public $classes = [];

    public $annotations = [
        FailCache::class,
    ];

    /**
     * @var CacheManager
     */
    protected $manager;

    /**
     * @var AnnotationManager
     */
    protected $annotationManager;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function __construct(CacheManager $manager, AnnotationManager $annotationManager, StdoutLoggerInterface $logger)
    {
        $this->manager = $manager;
        $this->annotationManager = $annotationManager;
        $this->logger = $logger;
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
        } catch (\Throwable $throwable) {
            [$has, $result] = $driver->fetch($key);
            if (! $has) {
                throw $throwable;
            }
            $this->logger->debug(sprintf('Returns fail cache [%s]', $key));
        }

        return $result;
    }
}
