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

namespace Hyperf\DistributedLock;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\DistributedLock\Annotation\Lock;
use Hyperf\DistributedLock\Exception\LockException;
use Hyperf\DistributedLock\Helper\StringHelper;

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

    public function getMutexKey(string $className, string $method, array $arguments, string $separator = ':'): array
    {
        /** @var Lock $annotation */
        $annotation = $this->getAnnotation(Lock::class, $className, $method);
        $prefix = $this->config->get('distributed-lock.prefix', 'lock');

        $key = $this->getFormattedKey($prefix . $separator . $annotation->mutex, $arguments, $annotation->value, $separator);
        $ttl = $annotation->ttl ?? $this->config->get('distributed-lock.ttl', 10);

        return [$key, $ttl, $annotation];
    }

    protected function getAnnotation(string $annotation, string $className, string $method): AbstractAnnotation
    {
        $collector = AnnotationCollector::get($className);
        $result = $collector['_m'][$method][$annotation] ?? null;
        if (! $result instanceof $annotation) {
            throw new LockException(sprintf('Annotation %s in %s:%s not exist.', $annotation, $className, $method));
        }

        return $result;
    }

    protected function getFormattedKey(string $prefix, array $arguments, ?string $value = null, string $separator = ':'): string
    {
        $key = StringHelper::format($prefix, $arguments, $value, $separator);

        if (strlen($key) > 64) {
            $this->logger->warning('The lock mutex key length is too long. The key is ' . $key);
        }

        return $key;
    }
}
