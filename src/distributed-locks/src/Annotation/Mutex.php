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

namespace Hyperf\DistributedLocks\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Mutex extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $prefix;

    /**
     * @var string
     */
    public $value;

    /**
     * @var int
     */
    public $ttl;

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->ttl = (int)$this->ttl;
    }

    public function collectMethod(string $className, ?string $target): void
    {
//        if (isset($this->listener)) {
//            CacheListenerCollector::set($this->listener, [
//                'className' => $className,
//                'method' => $target,
//            ]);
//        }

        AnnotationCollector::collectMethod($className, $target, static::class, $this);
    }
}
