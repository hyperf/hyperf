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

namespace Hyperf\Di\Annotation;

use PhpDocReader\PhpDocReader;
use Hyperf\Di\ReflectionManager;

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

    /**
     * {@inheritdoc}
     */
    public function collectProperty(string $className, ?string $target): void
    {
        if ($this->value !== null) {
            $this->value = $this->docReader->getPropertyClass(ReflectionManager::reflectClass($className)->getProperty($target));
            AnnotationCollector::collectProperty($className, $target, static::class, $this->value);
        }
    }
}
