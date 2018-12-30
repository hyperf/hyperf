<?php

namespace Hyperf\HttpServer\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class PostMapping extends AbstractAnnotation
{

    /**
     * @var string
     */
    public $path;

    public function __construct($value = null)
    {
        parent::__construct($value);
        if (isset($value['path'])) {
            $this->path = $value['path'];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function collect(string $className, ?string $target): void
    {
        if ($this->methods && $this->path) {
            AnnotationCollector::collectMethod($className, $target, static::class, [
                'methods' => ['POST'],
                'path' => $this->path,
            ]);
        }
    }

}