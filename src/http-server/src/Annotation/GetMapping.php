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

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class GetMapping extends AbstractAnnotation
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
     * {@inheritdoc}
     */
    public function collectMethod(string $className, ?string $target): void
    {
        if ($this->path) {
            AnnotationCollector::collectMethod($className, $target, self::class, [
                'methods' => ['GET'],
                'path' => $this->path,
            ]);
        }
    }
}
