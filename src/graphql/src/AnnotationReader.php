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
namespace Hyperf\GraphQL;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use Hyperf\GraphQL\Annotation\ExtendType;
use Hyperf\GraphQL\Annotation\Factory;
use Hyperf\GraphQL\Annotation\FailWith;
use Hyperf\GraphQL\Annotation\Logged;
use Hyperf\GraphQL\Annotation\Right;
use Hyperf\GraphQL\Annotation\SourceField;
use Hyperf\GraphQL\Annotation\Type;
use ReflectionClass;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Annotations\AbstractRequest;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\ClassNotFoundException;
use function in_array;
use function strpos;
use function substr;

class AnnotationReader
{
    // In this mode, no exceptions will be thrown for incorrect annotations (unless the name of the annotation we are looking for is part of the docblock)
    public const LAX_MODE = 'LAX_MODE';

    // In this mode, exceptions will be thrown for any incorrect annotations.
    public const STRICT_MODE = 'STRICT_MODE';

    /**
     * If true, no exceptions will be thrown for incorrect annotations in code coming from the "vendor/" directory.
     */
    private string $mode;

    private array $methodAnnotationCache = [];

    /**
     * AnnotationReader constructor.
     * @param string $mode One of self::LAX_MODE or self::STRICT_MODE
     * @param string[] $strictNamespaces
     */
    public function __construct(private Reader $reader, string $mode = self::STRICT_MODE, /**
     * Classes in those namespaces MUST have valid annotations (otherwise, an error is thrown).
     */
    private array $strictNamespaces = [])
    {
        if (! in_array($mode, [self::LAX_MODE, self::STRICT_MODE], true)) {
            throw new \InvalidArgumentException('The mode passed must be one of AnnotationReader::LAX_MODE, AnnotationReader::STRICT_MODE');
        }
        $this->mode = $mode;
    }

    public function getTypeAnnotation(ReflectionClass $refClass): ?Type
    {
        try {
            /** @var null|Type $type */
            $type = $this->getClassAnnotation($refClass, Type::class);
            if ($type !== null && $type->isSelfType()) {
                $type->setClass($refClass->getName());
            }
        } catch (ClassNotFoundException $e) {
            throw ClassNotFoundException::wrapException($e, $refClass->getName());
        }
        return $type;
    }

    public function getExtendTypeAnnotation(ReflectionClass $refClass): ?ExtendType
    {
        try {
            /** @var null|ExtendType $extendType */
            $extendType = $this->getClassAnnotation($refClass, ExtendType::class);
        } catch (ClassNotFoundException $e) {
            throw ClassNotFoundException::wrapExceptionForExtendTag($e, $refClass->getName());
        }
        return $extendType;
    }

    public function getRequestAnnotation(ReflectionMethod $refMethod, string $annotationName): ?AbstractRequest
    {
        return $this->getMethodAnnotation($refMethod, $annotationName);
    }

    public function getLoggedAnnotation(ReflectionMethod $refMethod): ?Logged
    {
        return $this->getMethodAnnotation($refMethod, Logged::class);
    }

    public function getRightAnnotation(ReflectionMethod $refMethod): ?Right
    {
        return $this->getMethodAnnotation($refMethod, Right::class);
    }

    public function getFailWithAnnotation(ReflectionMethod $refMethod): ?FailWith
    {
        return $this->getMethodAnnotation($refMethod, FailWith::class);
    }

    /**
     * @return SourceField[]
     */
    public function getSourceFields(ReflectionClass $refClass): array
    {
        return $this->getClassAnnotations($refClass, SourceField::class);
    }

    public function getFactoryAnnotation(ReflectionMethod $refMethod): ?Factory
    {
        return $this->getMethodAnnotation($refMethod, Factory::class);
    }

    /**
     * Returns the class annotations. Finds in the parents too.
     *
     * @return object[]
     */
    public function getClassAnnotations(ReflectionClass $refClass, string $annotationClass): array
    {
        $toAddAnnotations = [];
        do {
            try {
                $allAnnotations = $this->reader->getClassAnnotations($refClass);
                $toAddAnnotations[] = \array_filter($allAnnotations, fn($annotation): bool => $annotation instanceof $annotationClass);
            } catch (AnnotationException $e) {
                if ($this->mode === self::STRICT_MODE) {
                    throw $e;
                }
                if ($this->mode === self::LAX_MODE) {
                    if ($this->isErrorImportant($annotationClass, $refClass->getDocComment(), $refClass->getName())) {
                        throw $e;
                    }
                }
            }
            $refClass = $refClass->getParentClass();
        } while ($refClass);

        if (! empty($toAddAnnotations)) {
            return array_merge(...$toAddAnnotations);
        }
        return [];
    }

    /**
     * Returns a class annotation. Finds in the parents if not found in the main class.
     *
     * @return null|object
     */
    private function getClassAnnotation(ReflectionClass $refClass, string $annotationClass)
    {
        do {
            $type = null;
            try {
                $type = $this->reader->getClassAnnotation($refClass, $annotationClass);
            } catch (AnnotationException $e) {
                switch ($this->mode) {
                    case self::STRICT_MODE:
                        throw $e;
                    case self::LAX_MODE:
                        if ($this->isErrorImportant($annotationClass, $refClass->getDocComment(), $refClass->getName())) {
                            throw $e;
                        }
                            return null;
                    default:
                        throw new \RuntimeException("Unexpected mode '{$this->mode}'."); // @codeCoverageIgnore
                }
            }
            if ($type !== null) {
                return $type;
            }
            $refClass = $refClass->getParentClass();
        } while ($refClass);
        return null;
    }

    /**
     * Returns a method annotation and handles correctly errors.
     *
     * @return null|object
     */
    private function getMethodAnnotation(ReflectionMethod $refMethod, string $annotationClass)
    {
        $cacheKey = $refMethod->getDeclaringClass()->getName() . '::' . $refMethod->getName() . '_' . $annotationClass;
        if (isset($this->methodAnnotationCache[$cacheKey])) {
            return $this->methodAnnotationCache[$cacheKey];
        }

        try {
            return $this->methodAnnotationCache[$cacheKey] = $this->reader->getMethodAnnotation($refMethod, $annotationClass);
        } catch (AnnotationException $e) {
            switch ($this->mode) {
                case self::STRICT_MODE:
                    throw $e;
                case self::LAX_MODE:
                    if ($this->isErrorImportant($annotationClass, $refMethod->getDocComment(), $refMethod->getDeclaringClass()->getName())) {
                        throw $e;
                    }
                        return null;
                default:
                    throw new \RuntimeException("Unexpected mode '{$this->mode}'."); // @codeCoverageIgnore
            }
        }
    }

    /**
     * Returns true if the annotation class name is part of the docblock comment.
     */
    private function isErrorImportant(string $annotationClass, string $docComment, string $className): bool
    {
        foreach ($this->strictNamespaces as $strictNamespace) {
            if (str_starts_with($className, $strictNamespace)) {
                return true;
            }
        }
        $shortAnnotationClass = substr($annotationClass, strrpos($annotationClass, '\\') + 1);
        return str_contains($docComment, '@' . $shortAnnotationClass);
    }
}
