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
namespace Hyperf\Cache\Annotation;

use Hyperf\Cache\CacheListenerCollector;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class FailCache extends AbstractAnnotation
{
    public $prefix;

    public $value;

    public $ttl;

    public $listener;

    public $group = 'default';

    public function collectMethod(string $className, ?string $target): void
    {
        if (isset($this->listener)) {
            CacheListenerCollector::setListener($this->listener, [
                'className' => $className,
                'method' => $target,
            ]);
        }

        AnnotationCollector::collectMethod($className, $target, static::class, $this);
    }
}
