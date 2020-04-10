<?php


namespace Hyperf\WebSocketServer\SocketIO;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Event extends AbstractAnnotation
{
    public $value;

    public function __construct($value = null)
    {
        $this->value = $value["value"];
    }

    public function collectMethod(string $className, ?string $target): void
    {
        EventAnnotationCollector::collectEvent($className, $target, $this);
    }
}
