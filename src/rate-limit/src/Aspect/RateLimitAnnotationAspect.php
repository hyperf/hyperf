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

namespace Hyperf\RateLimit\Aspect;

use bandwidthThrottle\tokenBucket\storage\StorageException;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\RateLimit\Exception\RateLimitException;
use Hyperf\RateLimit\Handler\RateLimitHandler;
use Swoole\Coroutine;

/**
 * @Aspect
 */
class RateLimitAnnotationAspect implements AroundInterface
{
    public $classes = [];

    public $annotations = [
        RateLimit::class,
    ];

    /**
     * @var array
     */
    private $annotationProperty;

    /**
     * @var array
     */
    private $config;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RateLimitHandler
     */
    private $rateLimitHandler;

    public function __construct(ConfigInterface $config, RequestInterface $request, RateLimitHandler $rateLimitHandler)
    {
        $this->annotationProperty = get_object_vars(new RateLimit());
        $this->config = $config->get('rate-limit', []);
        $this->request = $request;
        $this->rateLimitHandler = $rateLimitHandler;
    }

    /**
     * @throws RateLimitException limit but without handle
     * @throws StorageException when the storage driver bootstrap failed
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $annotation = $this->getWeightingAnnotation($this->getAnnotations($proceedingJoinPoint));

        $bucketKey = $annotation->key;
        if (is_callable($bucketKey)) {
            $bucketKey = $bucketKey($proceedingJoinPoint);
        }
        if (! $bucketKey) {
            $bucketKey = $this->request->getUri()->getPath();
        }

        $bucket = $this->rateLimitHandler->build($bucketKey, $annotation->create, $annotation->capacity, $annotation->waitTimeout);

        $maxTime = microtime(true) + $annotation->waitTimeout;
        $seconds = 0;

        while (true) {
            try {
                if ($bucket->consume($annotation->consume ?? 1, $seconds)) {
                    return $proceedingJoinPoint->process();
                }
            } catch (StorageException $exception) {
            }
            if (microtime(true) + $seconds > $maxTime) {
                break;
            }
            Coroutine::sleep($seconds > 0.001 ? $seconds : 0.001);
        }

        if (! $annotation->limitCallback || ! is_callable($annotation->limitCallback)) {
            throw new RateLimitException('Service Unavailable.', 503);
        }
        return call_user_func($annotation->limitCallback, $seconds, $proceedingJoinPoint);
    }

    /**
     * @param RateLimit[] $annotations
     */
    public function getWeightingAnnotation(array $annotations): RateLimit
    {
        $property = array_merge($this->annotationProperty, $this->config);
        foreach ($annotations as $annotation) {
            if (! $annotation) {
                continue;
            }
            $property = array_merge($property, array_filter(get_object_vars($annotation)));
        }
        return new RateLimit($property);
    }

    public function getAnnotations(ProceedingJoinPoint $proceedingJoinPoint): array
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        return [
            $metadata->class[RateLimit::class] ?? null,
            $metadata->method[RateLimit::class] ?? null,
        ];
    }
}
