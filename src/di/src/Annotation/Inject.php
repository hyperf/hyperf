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
namespace Hyperf\Di\Annotation;

use Hyperf\Di\BetterReflectionManager;
use Hyperf\Di\Exception\AnnotationException;
use Hyperf\Di\TypesFinderManager;
use phpDocumentor\Reflection\Types\Object_;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Inject extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $value;

    /**
     * @var bool
     */
    public $required = true;

    /**
     * @var bool
     */
    public $lazy = false;

    public function __construct($value = null)
    {
        parent::__construct($value);
    }

    public function collectProperty(string $className, ?string $target): void
    {
        try {
            $reflectionClass = BetterReflectionManager::reflectClass($className);
            $properties = $reflectionClass->getImmediateProperties();
            $reflectionProperty = $properties[$target] ?? null;
            if (! $reflectionProperty) {
                $this->value = '';
                return;
            }
            if ($reflectionProperty->hasType()) {
                $this->value = $reflectionProperty->getType()->getName();
            } else {
                $reflectionTypes = TypesFinderManager::getPropertyFinder()->__invoke($reflectionProperty, $reflectionClass->getDeclaringNamespaceAst());
                if (isset($reflectionTypes[0]) && $reflectionTypes[0] instanceof Object_) {
                    $this->value = ltrim((string) $reflectionTypes[0], '\\');
                }
            }

            if (empty($this->value)) {
                throw new AnnotationException("The @Inject value is invalid for {$className}->{$target}");
            }

            if ($this->lazy) {
                $this->value = 'HyperfLazy\\' . $this->value;
            }
            AnnotationCollector::collectProperty($className, $target, static::class, $this);
        } catch (AnnotationException $exception) {
            if ($this->required) {
                throw $exception;
            }
            $this->value = '';
        }
    }
}
