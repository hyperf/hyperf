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
    public $key;

    public $ttl;

    public $listener;

    public $group = 'default';

    public function __construct($value = null)
    {
        if (isset($value['value'])) {
            $this->key = $value['value'];
        }

        if (isset($value['key'])) {
            $this->key = $value['key'];
        }

        if (isset($value['ttl'])) {
            $this->ttl = $value['ttl'];
        }

        if (isset($value['listener'])) {
            $this->listener = $value['listener'];
        }

        if (isset($value['group'])) {
            $this->group = $value['group'];
        }
    }

    public function collectMethod(string $className, ?string $target): void
    {
        if (! isset($this->key)) {
            $this->key = 'cache:' . $className . ':' . $target;
        }

        if (isset($this->listener)) {
            CacheListenerCollector::set($this->listener, [
                'className' => $className,
                'method' => $target,
            ]);
        }

        AnnotationCollector::collectMethod($className, $target, static::class, [
            'key' => $this->key,
            'ttl' => $this->ttl,
            'listener' => $this->listener,
            'group' => $this->group,
        ]);
    }
}
