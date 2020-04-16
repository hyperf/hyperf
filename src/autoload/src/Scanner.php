<?php

namespace Hyperf\Autoload;


use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Hyperf\Di\Annotation\AnnotationInterface;
use Hyperf\Di\BetterReflectionManager;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\AutoloadSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\DirectoriesSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\EvaledCodeSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\MemoizingSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\PhpInternalSourceLocator;
use Roave\BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use Roave\BetterReflection\Util\Autoload\ClassLoader;
use Roave\BetterReflection\Util\Autoload\ClassLoaderMethod\FileCacheLoader;

class Scanner
{

    /**
     * @var \Hyperf\Autoload\ClassLoader
     */
    protected $classloader;

    public function __construct(\Hyperf\Autoload\ClassLoader $classloader, array $ignoreAnnotations = [], array $globalImports = [])
    {
        $this->classloader = $classloader;
        AnnotationRegistry::registerLoader(function ($class) {
            return class_exists($class, false);
        });

        foreach ($ignoreAnnotations as $annotation) {
            AnnotationReader::addGlobalIgnoredName($annotation);
        }
        foreach ($globalImports as $alias => $annotation) {
            AnnotationReader::addGlobalImports($alias, $annotation);
        }

    }

    public function scan(array $paths): array
    {
        try {
            $classes = [];
            if (! $paths) {
                return $classes;
            }
            $paths = $this->normalizeDir($paths);

            $reflector = BetterReflectionManager::initClassReflector($paths);
            $classes = $reflector->getAllClasses();

            $annotationReader = new AnnotationReader();

            foreach ($classes as $reflectionClass) {
                $className = $reflectionClass->getName();
                // echo '[Scan] ' . $className . PHP_EOL;
                // Parse class annotations
                $classAnnotations = $annotationReader->getClassAnnotations($reflectionClass);
                if (! empty($classAnnotations)) {
                    foreach ($classAnnotations as $classAnnotation) {
                        if ($classAnnotation instanceof AnnotationInterface) {
                            $classAnnotation->collectClass($className);
                        }
                    }
                }
                // Parse properties annotations
                $properties = $reflectionClass->getImmediateProperties();
                foreach ($properties as $property) {
                    $propertyAnnotations = $annotationReader->getPropertyAnnotations($property);
                    if (! empty($propertyAnnotations)) {
                        foreach ($propertyAnnotations as $propertyAnnotation) {
                            if ($propertyAnnotation instanceof AnnotationInterface) {
                                $propertyAnnotation->collectProperty($className, $property->getName());
                            }
                        }
                    }
                }
                // Parse methods annotations
                $methods = $reflectionClass->getImmediateMethods();
                foreach ($methods as $method) {
                    $methodAnnotations = $annotationReader->getMethodAnnotations($method);
                    if (! empty($methodAnnotations)) {
                        foreach ($methodAnnotations as $methodAnnotation) {
                            if ($methodAnnotation instanceof AnnotationInterface) {
                                $methodAnnotation->collectMethod($className, $method->getName());
                            }
                        }
                    }
                }
                unset($reflectionClass, $classAnnotations, $properties, $methods);
            }
            unset($finder, $astLocator, $annotationReader);
        } catch (\Throwable $throwable) {
            echo $throwable->getMessage() . PHP_EOL;
            // var_dump((string)$throwable);
        }
        return $classes;
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
