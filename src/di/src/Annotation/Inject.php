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

use Hyperf\Di\ReflectionManager;
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
