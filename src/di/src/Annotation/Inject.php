<?php

namespace Hyperflex\Di\Annotation;


use Hyperflex\Di\ReflectionManager;
use PhpDocReader\PhpDocReader;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Inject extends AbstractAnnotation
{
    /**
     * @var PhpDocReader
     */
    private $docReader;

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->docReader = new PhpDocReader();
    }

    public function collect(string $className, ?string $target): void
    {
        if (! $this->value) {
            $this->value = $this->docReader->getPropertyClass(ReflectionManager::reflectClass($className)->getProperty($target));
        }
        if (isset($this->value)) {
            AnnotationCollector::collectProperty($className, $target, static::class, $this->value);
        }
    }


}