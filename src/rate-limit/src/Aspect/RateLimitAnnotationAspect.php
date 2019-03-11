<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\RateLimit\Aspect;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\ArroundInterface;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\RateLimit\Annotation\RateLimit;
use Hyperf\RateLimit\Exception\RateLimiterException;
use Hyperf\RateLimit\Handler\RateLimitHandler;

/**
 * @Aspect
 */
class RateLimitAnnotationAspect implements ArroundInterface
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
     * @var ConfigInterface
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
        $this->config = $config;
        $this->request = $request;
        $this->rateLimitHandler = $rateLimitHandler;
    }

    /**
     * @param ProceedingJoinPoint $proceedingJoinPoint
     * @throws \bandwidthThrottle\tokenBucket\storage\StorageException
     * @return mixed
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $annotation = $this->getWeightingAnnotation($this->getAnnotations($proceedingJoinPoint));

        $bucketsKey = $annotation->bucketsKey;
        if (is_callable($bucketsKey)) {
            $bucketsKey = $bucketsKey($proceedingJoinPoint);
        }
        if (! $bucketsKey) {
            $bucketsKey = trim(str_replace('/', ':', $this->request->getUri()->getPath()), ':');
        }

        $bucket = $this->rateLimitHandler->getBucket($bucketsKey);
        if (! $bucket) {
            $bucket = $this->rateLimitHandler->build($bucketsKey, $annotation->limit, $annotation->capacity);
        }

        if ($bucket->consume($annotation->demand, $seconds)) {
            return $proceedingJoinPoint->process();
        }

        if (! $annotation->callback || ! is_callable($annotation->callback)) {
            throw new RateLimiterException('Request rate limit');
        }
        return call_user_func($annotation->callback, $seconds, $proceedingJoinPoint);
    }

    /**
     * @param RateLimit[] $annotations
     * @return RateLimit
     */
    public function getWeightingAnnotation(array $annotations)
    {
        $property = array_merge($this->annotationProperty, $this->config->get('rate-limit', []));
        foreach ($annotations as $annotation) {
            if (! $annotation) {
                continue;
            }
            $property = array_merge($property, array_filter(get_object_vars($annotation)));
        }
        return new RateLimit($property);
    }

    public function getAnnotations(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $metadata = $proceedingJoinPoint->getAnnotationMetadata();
        return [
            $metadata->class[RateLimit::class] ?? null,
            $metadata->method[RateLimit::class] ?? null,
        ];
    }
}
