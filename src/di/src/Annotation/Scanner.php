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

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Hyperf\Di\Aop\Ast;
use Hyperf\Di\Aop\AstCollector;
use Hyperf\Di\ReflectionManager;
use Symfony\Component\Finder\Finder;

class Scanner
{
    /**
     * @var Ast
     */
    private $parser;

    /**
     * @var array
     */
    private $ignoreAnnotations = [];

    public function __construct(array $ignoreAnnotations = ['mixin'])
    {
        $this->parser = new Ast();
        $this->ignoreAnnotations = $ignoreAnnotations;

        // TODO: this method is deprecated and will be removed in doctrine/annotations 2.0
        AnnotationRegistry::registerLoader('class_exists');
    }

    public function scan(array $paths)
    {
        $paths = $this->normalizeDir($paths);

        $finder = new Finder();
        $finder->files()->in($paths)->name('*.php');

        array_walk($this->ignoreAnnotations, function ($value) {
            AnnotationReader::addGlobalIgnoredName($value);
        });
        $reader = new AnnotationReader();
        $classColletion = [];
        foreach ($finder as $file) {
            try {
                $stmts = $this->parser->parse($file->getContents());
                $className = $this->parser->parseClassByStmts($stmts);
                if (! $className) {
                    continue;
                }
                AstCollector::set($className, $stmts);
                $classColletion[] = $className;
            } catch (\RuntimeException $e) {
                continue;
            }
        }
        // Because the annotation class should loaded before use it, so load file via $finder previous, and then parse annotation here.
        foreach ($classColletion as $className) {
            $reflectionClass = ReflectionManager::reflectClass($className);
            $annotations = $reader->getClassAnnotations($reflectionClass);
            foreach ($annotations as $annotation) {
                if ($annotation instanceof AbstractAnnotation) {
                    $annotation->collectClass($className, null);
                }
            }

            // Parse properties annotations.
            $properties = $reflectionClass->getProperties();
            foreach ($properties as $property) {
                $propertyAnnotations = $reader->getPropertyAnnotations($property);
                if (! empty($propertyAnnotations)) {
                    foreach ($propertyAnnotations as $propertyAnnotation) {
                        $propertyAnnotation instanceof AnnotationInterface && $propertyAnnotation->collectProperty($className, $property->getName());
                    }
                }
            }

            // Parse methods annotations.
            $methods = $reflectionClass->getMethods();
            foreach ($methods as $method) {
                $methodAnnotations = $reader->getMethodAnnotations($method);
                if (! empty($methodAnnotations)) {
                    foreach ($methodAnnotations as $methodAnnotation) {
                        $methodAnnotation instanceof AnnotationInterface && $methodAnnotation->collectMethod($className, $method->getName());
                    }
                }
            }
        }

        return $classColletion;
    }

    /**
     * Normalizes given directory names by removing directory not exist.
     *
     * @param array $dir
     *
     * @return array
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
