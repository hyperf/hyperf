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
        parent::__construct($value);
        if (isset($value['methods'])) {
            // Explode a string to a array
            $this->methods = explode(',', Str::upper(str_replace(' ', '', $value['methods'])));
        }
        if (isset($value['path'])) {
            $this->path = $value['path'];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function collectMethod(string $className, ?string $target): void
    {
        if ($this->methods && $this->path) {
            AnnotationCollector::collectMethod($className, $target, static::class, [
                'methods' => $this->methods,
                'path' => $this->path,
            ]);
        }
    }
}
