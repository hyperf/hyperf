<?php

namespace Hyperf\HttpServer\Annotation;


use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Utils\Str;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class RequestMapping extends AbstractAnnotation
{

    /**
     * @var array
     */
    public $methods;

    /**
     * @var string
     */
    public $path;

    public function __construct($value = null)
    {
        $this->value = $value;
        if (isset($value['methods'])) {
            // Explode a string to a array
            $this->methods = explode(',', Str::upper(str_replace(' ', '', $value['methods'])));
        }
        if (isset($value['path'])) {
            $this->path = $value['path'];
        }
    }

    public function collect(string $className, ?string $target): void
    {
        if ($this->methods && $this->path) {
            AnnotationCollector::collectMethod($className, $target, static::class, [
                'methods' => $this->methods,
                'path' => $this->path,
            ]);
        }
    }

}