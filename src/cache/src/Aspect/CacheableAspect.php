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

use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Cache\AnnotationManager;
use Hyperf\Cache\CacheManager;
use Hyperf\Cache\Driver\DriverInterface;
use Hyperf\Cache\Driver\KeyCollectorInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

/**
 * @Aspect
 */
class CacheableAspect extends AbstractAspect
{
    public $classes = [];

    public $annotations = [
        Cacheable::class,
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
    protected $logger;

    /**
     * 下一次可从缓存中读取的时间，0 为永久.
     *
     * @var int
     */
    private $nextCacheable = 0;

    public function __construct(CacheManager $manager, AnnotationManager $annotationManager, StdoutLoggerInterface $logger)
    {
        $this->manager = $manager;
        $this->annotationManager = $annotationManager;
        $this->logger = $logger;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        if ($this->nextCacheable > time()) {
            return $proceedingJoinPoint->process();
        }

        $className = $proceedingJoinPoint->className;
        $method = $proceedingJoinPoint->methodName;
        $arguments = $proceedingJoinPoint->arguments['keys'];

        [$key, $ttl, $group, $annotation] = $this->annotationManager->getCacheableValue($className, $method, $arguments);

        $driver = $this->manager->getDriver($group);

        [$has, $result] = $this->fetch($driver, $key, $this->annotationManager->getCacheableResetTimeout($className, $method));
        if ($has) {
            return $result;
        }

        $result = $proceedingJoinPoint->process();

        $this->set($driver, $annotation, $key, $result, $ttl);

        return $result;
    }

    protected function fetch(DriverInterface $driver, string $key, int $resetTimeout): array
    {
        try {
            return $driver->fetch($key);
        } catch (Throwable $e) {
            $this->nextCacheable = time() + $resetTimeout;

            $this->logger->error('[Cacheable] failed to fetch from cache driver, and downgraded to source');
        }

        return [false, null];
    }

    protected function set(DriverInterface $driver, AbstractAnnotation $annotation, string $key, $result, int $ttl): void
    {
        try {
            $driver->set($key, $result, $ttl);
            if ($driver instanceof KeyCollectorInterface && $annotation instanceof Cacheable && $annotation->collect) {
                $driver->addKey($annotation->prefix . 'MEMBERS', $key);
            }
        } catch (InvalidArgumentException $e) {
            $this->logger->error('[Cacheable] failed to cache because of error argument, and downgraded to do nothing');
        } catch (Throwable $e) {
            $this->logger->error('[Cacheable] failed to cache, and downgraded to do nothing');
        }
    }
}
