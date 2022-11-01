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

use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\Types\Object_;
use phpDocumentor\Reflection\Types\Self_;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use TheCodingMachine\GraphQLite\MissingTypeHintException;
use TheCodingMachine\GraphQLite\NamingStrategyInterface;

class InputTypeUtils
{
    /**
     * @var AnnotationReader
     */
    private $annotationReader;

    /**
     * @var NamingStrategyInterface
     */
    private $namingStrategy;

    public function __construct(
        AnnotationReader $annotationReader,
        NamingStrategyInterface $namingStrategy
    ) {
        $this->annotationReader = $annotationReader;
        $this->namingStrategy = $namingStrategy;
    }

    /**
     * Returns an array with 2 elements: [ $inputName, $className ].
     *
     * @return string[]
     */
    public function getInputTypeNameAndClassName(ReflectionMethod $method): array
    {
        $fqsen = ltrim((string) $this->validateReturnType($method), '\\');
        $factory = $this->annotationReader->getFactoryAnnotation($method);
        if ($factory === null) {
            throw new RuntimeException($method->getDeclaringClass()->getName() . '::' . $method->getName() . ' has no @Factory annotation.');
        }
        return [$this->namingStrategy->getInputTypeName($fqsen, $factory), $fqsen];
    }

    private function validateReturnType(ReflectionMethod $refMethod): Fqsen
    {
        $returnType = $refMethod->getReturnType();
        if ($returnType === null) {
            throw MissingTypeHintException::missingReturnType($refMethod);
        }

        if ($returnType->allowsNull()) {
            throw MissingTypeHintException::nullableReturnType($refMethod);
        }

        $type = (string) $returnType;

        $typeResolver = new \phpDocumentor\Reflection\TypeResolver();

        $phpdocType = $typeResolver->resolve($type);
        $phpdocType = $this->resolveSelf($phpdocType, $refMethod->getDeclaringClass());
        if (! $phpdocType instanceof Object_) {
            throw MissingTypeHintException::invalidReturnType($refMethod);
        }

        return $phpdocType->getFqsen();
    }

    /**
     * Resolves "self" types into the class type.
     */
    private function resolveSelf(Type $type, ReflectionClass $reflectionClass): Type
    {
        if ($type instanceof Self_) {
            return new Object_(new Fqsen('\\' . $reflectionClass->getName()));
        }
        return $type;
    }
}
