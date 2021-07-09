<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\HttpServer\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\MultipleAnnotation;
use Hyperf\Di\Annotation\MultipleAnnotationInterface;

/**
 * @Annotation
 * @Target({"ALL"})
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Middleware extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $middleware = '';

    public function __construct(...$value)
    {
        parent::__construct(...$value);
        $this->bindMainProperty('middleware', $value);
    }

    public function collectClass(string $className): void
    {
        $annotation = AnnotationCollector::getClassAnnotation($className, self::class);
        if ($annotation instanceof MultipleAnnotationInterface) {
            $annotation->insert($this);
        } else {
            $annotation = new MultipleAnnotation($this);
        }

        AnnotationCollector::collectClass($className, self::class, $annotation);
    }

    public function collectMethod(string $className, ?string $target): void
    {
        $annotation = AnnotationCollector::getClassMethodAnnotation($className, $target)[self::class] ?? null;
        if ($annotation instanceof MultipleAnnotationInterface) {
            $annotation->insert($this);
        } else {
            $annotation = new MultipleAnnotation($this);
        }

        AnnotationCollector::collectMethod($className, $target, self::class, $annotation);
    }
}
