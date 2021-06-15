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
namespace Hyperf\SocketIOServer\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\ReflectionManager;
use Hyperf\SocketIOServer\Collector\EventAnnotationCollector;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Event extends AbstractAnnotation
{
    public $event = 'event';

    public function __construct(...$value)
    {
        $value = parent::__construct(...$value);
        $this->bindMainProperty('event', $value);
    }

    public function collectMethod(string $className, ?string $target): void
    {
        EventAnnotationCollector::collectEvent($className, $target, $this);
        parent::collectMethod($className, $target);
    }

    public function collectClass(string $className): void
    {
        $methods = ReflectionManager::reflectClass($className)->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $target = $method->getName();
            EventAnnotationCollector::collectEvent($className, $target, new Event(['value' => $target]));
        }
        parent::collectClass($className);
    }
}
