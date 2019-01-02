<?php

namespace Hyperf\Event\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class TaskListener extends AbstractAnnotation
{

    /**
     * @var int
     */
    public $priority = 1;

    public function __construct($value = null)
    {
        parent::__construct($value);
        if (isset($value['priority']) && is_numeric($value['priority'])) {
            $this->priority = (int)$value['priority'];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function collectClass(string $className, ?string $target): void
    {
        if (null !== $this->value) {
            AnnotationCollector::collectClass($className, static::class, [
                'priority' => $this->priority,
            ]);
        }
    }

}