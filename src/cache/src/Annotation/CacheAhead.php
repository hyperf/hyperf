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

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * Only Support Redis Driver.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class CacheAhead extends AbstractAnnotation
{
    /**
     * @param null|int $ttl the max offset for ttl
     */
    public function __construct(
        public ?string $prefix = null,
        public ?string $value = null,
        public ?int $ttl = null,
        public int $aheadSeconds = 0,
        public int $lockSeconds = 10,
        public int $offset = 0,
        public string $group = 'default',
        public bool $collect = false,
        public ?array $skipCacheResults = null
    ) {
    }

    public function collectMethod(string $className, ?string $target): void
    {
        AnnotationCollector::collectMethod($className, $target, static::class, $this);
    }
}
