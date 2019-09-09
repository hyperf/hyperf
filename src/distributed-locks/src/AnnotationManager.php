<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\DistributedLocks;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\DistributedLocks\Annotation\Lock;
use Hyperf\DistributedLocks\Exception\LockException;
use Hyperf\DistributedLocks\Helper\StringHelper;

class AnnotationManager
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(ConfigInterface $config, StdoutLoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function getLockValue(string $className, string $method, array $arguments): array
    {
        /** @var Lock $annotation */
        $annotation = $this->getAnnotation(Lock::class, $className, $method);

        $key = $this->getFormatedKey($annotation->mutex, $arguments, $annotation->value);
        $ttl = $annotation->ttl ?? $this->config->get("distributed-locks.ttl", 10);

        return [$key, $ttl, $annotation];
    }

    protected function getAnnotation(string $annotation, string $className, string $method): AbstractAnnotation
    {
        $collector = AnnotationCollector::get($className);
        $result    = $collector['_m'][$method][$annotation] ?? null;
        if (!$result instanceof $annotation) {
            throw new LockException(sprintf('Annotation %s in %s:%s not exist.', $annotation, $className, $method));
        }

        return $result;
    }

    protected function getFormatedKey(string $prefix, array $arguments, ?string $value = null): string
    {
        $key = StringHelper::format($prefix, $arguments, $value);

        if (strlen($key) > 64) {
            $this->logger->warning('The lock key length is too long. The key is ' . $key);
        }

        return $key;
    }
}
