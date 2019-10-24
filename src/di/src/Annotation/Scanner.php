<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Di\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Hyperf\Di\Aop\Ast;
use Hyperf\Di\ReflectionManager;
use Symfony\Component\Finder\Finder;

class Scanner
{
    /**
     * @var Ast
     */
    private $parser;

    public function __construct(array $ignoreAnnotations = ['mixin'])
    {
        $this->parser = new Ast();

        // TODO: this method is deprecated and will be removed in doctrine/annotations 2.0
        AnnotationRegistry::registerLoader('class_exists');

        foreach ($ignoreAnnotations as $annotation) {
            AnnotationReader::addGlobalIgnoredName($annotation);
        }
    }

    public function scan(array $paths): array
    {
        if (! $paths) {
            return [];
        }
        $paths = $this->normalizeDir($paths);

        $finder = new Finder();
        $finder->files()->in($paths)->name('*.php');

        $meta = [];
        foreach ($finder as $file) {
            try {
                $stmts = $this->parser->parse($file->getContents());
                $className = $this->parser->parseClassByStmts($stmts);
                if (! $className) {
                    continue;
                }
                $meta[$className] = $stmts;
            } catch (\RuntimeException $e) {
                continue;
            }
        }
        $this->collect(array_keys($meta));

        return $meta;
    }

    public function collect($classCollection)
    {
        $reader = new AnnotationReader();
        // Because the annotation class should loaded before use it, so load file via $finder previous, and then parse annotation here.
        foreach ($classCollection as $className) {
            $reflectionClass = ReflectionManager::reflectClass($className);
            $classAnnotations = $reader->getClassAnnotations($reflectionClass);
            if (! empty($classAnnotations)) {
                foreach ($classAnnotations as $classAnnotation) {
                    if ($classAnnotation instanceof AnnotationInterface) {
                        $classAnnotation->collectClass($className);
                    }
                }
            }

            // Parse properties annotations.
            $properties = $reflectionClass->getProperties();
            foreach ($properties as $property) {
                $propertyAnnotations = $reader->getPropertyAnnotations($property);
                if (! empty($propertyAnnotations)) {
                    foreach ($propertyAnnotations as $propertyAnnotation) {
                        if ($propertyAnnotation instanceof AnnotationInterface) {
                            $propertyAnnotation->collectProperty($className, $property->getName());
                        }
                    }
                }
            }

            // Parse methods annotations.
            $methods = $reflectionClass->getMethods();
            foreach ($methods as $method) {
                $methodAnnotations = $reader->getMethodAnnotations($method);
                if (! empty($methodAnnotations)) {
                    foreach ($methodAnnotations as $methodAnnotation) {
                        if ($methodAnnotation instanceof AnnotationInterface) {
                            $methodAnnotation->collectMethod($className, $method->getName());
                        }
                    }
                }
            }
        }
    }

    /**
     * Normalizes given directory names by removing directory not exist.
     */
    public function normalizeDir(array $paths): array
    {
        $result = [];
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $result[] = $path;
            }
        }

        return $result;
    }
}
