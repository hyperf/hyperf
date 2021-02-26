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
class Cacheable extends AbstractAnnotation
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
     * @var null|int
     */
    public $ttl;

    /**
     * @var string
     */
    public $listener;

    /**
     * The max offset for ttl.
     * @var int
     */
    public $offset = 0;

    /**
     * @var string
     */
    public $group = 'default';

    /**
     * @var bool
     */
    public $collect = false;

    public function __construct($value = null)
    {
        parent::__construct($value);
        if ($this->ttl !== null) {
            $this->ttl = (int) $this->ttl;
        }
        $this->offset = (int) $this->offset;
    }

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
