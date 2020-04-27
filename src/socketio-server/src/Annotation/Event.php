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
namespace Hyperf\SocketIOServer\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\SocketIOServer\Collector\EventAnnotationCollector;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Event extends AbstractAnnotation
{
    public $value;

    public function __construct($value = [])
    {
        parent::__construct();
        $this->value = $value['value'] ?? 'event';
    }

    public function collectMethod(string $className, ?string $target): void
    {
        EventAnnotationCollector::collectEvent($className, $target, $this);
        parent::collectMethod($className, $target);
    }
}
