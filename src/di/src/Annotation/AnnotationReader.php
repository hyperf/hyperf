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

use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\DocParser;
use Doctrine\Common\Annotations\PhpParser;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * A reader for docblock annotations.
 */
class AnnotationReader implements Reader
{
    /**
     * Global map for imports.
     *
     * @var array
     */
    private static $globalImports = [
        'ignoreannotation' => 'Doctrine\Common\Annotations\Annotation\IgnoreAnnotation',
    ];

    /**
     * A list with annotations that are not causing exceptions when not resolved to an annotation class.
     *
     * The names are case sensitive.
     *
     * @var array
     */
    private static $globalIgnoredNames = [
        // Annotation tags
        'Annotation' => true, 'Attribute' => true, 'Attributes' => true,
        /* Can we enable this? 'Enum' => true, */
        'Required' => true,
        'Target' => true,
        // Widely used tags (but not existent in phpdoc)
        'fix' => true, 'fixme' => true,
        'override' => true,
        // PHPDocumentor 1 tags
        'abstract' => true, 'access' => true,
        'code' => true,
        'deprec' => true,
        'endcode' => true, 'exception' => true,
        'final' => true,
        'ingroup' => true, 'inheritdoc' => true, 'inheritDoc' => true,
        'magic' => true,
        'name' => true,
        'toc' => true, 'tutorial' => true,
        'private' => true,
        'static' => true, 'staticvar' => true, 'staticVar' => true,
        'throw' => true,
        // PHPDocumentor 2 tags.
        'api' => true, 'author' => true,
        'category' => true, 'copyright' => true,
        'deprecated' => true,
        'example' => true,
        'filesource' => true,
        'global' => true,
        'ignore' => true, /* Can we enable this? 'index' => true, */ 'internal' => true,
        'license' => true, 'link' => true,
        'method' => true,
        'package' => true, 'param' => true, 'property' => true, 'property-read' => true, 'property-write' => true,
        'return' => true,
        'see' => true, 'since' => true, 'source' => true, 'subpackage' => true,
        'throws' => true, 'todo' => true, 'TODO' => true,
        'usedby' => true, 'uses' => true,
        'var' => true, 'version' => true,
        // PHPUnit tags
        'codeCoverageIgnore' => true, 'codeCoverageIgnoreStart' => true, 'codeCoverageIgnoreEnd' => true,
        // PHPCheckStyle
        'SuppressWarnings' => true,
        // PHPStorm
        'noinspection' => true,
        // PEAR
        'package_version' => true,
        // PlantUML
        'startuml' => true, 'enduml' => true,
        // Symfony 3.3 Cache Adapter
        'experimental' => true,
        // Slevomat Coding Standard
        'phpcsSuppress' => true,
        // PHP CodeSniffer
        'codingStandardsIgnoreStart' => true,
        'codingStandardsIgnoreEnd' => true,
        // PHPStan
        'template' => true, 'implements' => true, 'extends' => true, 'use' => true,
    ];

    /**
     * A list with annotations that are not causing exceptions when not resolved to an annotation class.
     *
     * The names are case sensitive.
     *
     * @var array
     */
    private static $globalIgnoredNamespaces = [];

    /**
     * Annotations parser.
     *
     * @var \Doctrine\Common\Annotations\DocParser
     */
    private $parser;

    /**
     * Annotations parser used to collect parsing metadata.
     *
     * @var \Doctrine\Common\Annotations\DocParser
     */
    private $preParser;

    /**
     * PHP parser used to collect imports.
     *
     * @var \Doctrine\Common\Annotations\PhpParser
     */
    private $phpParser;

    /**
     * In-memory cache mechanism to store imported annotations per class.
     *
     * @var array
     */
    private $imports = [];

    /**
     * In-memory cache mechanism to store ignored annotations per class.
     *
     * @var array
     */
    private $ignoredAnnotationNames = [];

    /**
     * Constructor.
     *
     * Initializes a new AnnotationReader.
     *
     * @param DocParser $parser
     *
     * @throws AnnotationException
     */
    public function __construct(DocParser $parser = null)
    {
        if (extension_loaded('Zend Optimizer+') && (ini_get('zend_optimizerplus.save_comments') === '0' || ini_get('opcache.save_comments') === '0')) {
            throw AnnotationException::optimizerPlusSaveComments();
        }

        if (extension_loaded('Zend OPcache') && ini_get('opcache.save_comments') == 0) {
            throw AnnotationException::optimizerPlusSaveComments();
        }

        // Make sure that the IgnoreAnnotation annotation is loaded
        class_exists(IgnoreAnnotation::class);

        $this->parser = $parser ?: new DocParser();

        $this->preParser = new DocParser();

        $this->preParser->setImports(self::$globalImports);
        $this->preParser->setIgnoreNotImportedAnnotations(true);
        $this->preParser->setIgnoredAnnotationNames(self::$globalIgnoredNames);

        $this->phpParser = new PhpParser();
    }

    public static function addGlobalImports(string $alias, string $annotation)
    {
        self::$globalImports[strtolower($alias)] = $annotation;
    }

    /**
     * Add a new annotation to the globally ignored annotation names with regard to exception handling.
     *
     * @param string $name
     */
    public static function addGlobalIgnoredName($name)
    {
        self::$globalIgnoredNames[$name] = true;
    }

    /**
     * Add a new annotation to the globally ignored annotation namespaces with regard to exception handling.
     *
     * @param string $namespace
     */
    public static function addGlobalIgnoredNamespace($namespace)
    {
        self::$globalIgnoredNamespaces[$namespace] = true;
    }

    public function getClassAnnotations(ReflectionClass $class)
    {
        $this->parser->setTarget(Target::TARGET_CLASS);
        $this->parser->setImports($this->getClassImports($class));
        $this->parser->setIgnoredAnnotationNames($this->getIgnoredAnnotationNames($class));
        $this->parser->setIgnoredAnnotationNamespaces(self::$globalIgnoredNamespaces);

        return $this->parser->parse($class->getDocComment(), 'class ' . $class->getName());
    }

    public function getClassAnnotation(ReflectionClass $class, $annotationName)
    {
        $annotations = $this->getClassAnnotations($class);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    public function getPropertyAnnotations(ReflectionProperty $property)
    {
        $class = $property->getDeclaringClass();
        $context = 'property ' . $class->getName() . '::$' . $property->getName();

        $this->parser->setTarget(Target::TARGET_PROPERTY);
        $this->parser->setImports($this->getPropertyImports($property));
        $this->parser->setIgnoredAnnotationNames($this->getIgnoredAnnotationNames($class));
        $this->parser->setIgnoredAnnotationNamespaces(self::$globalIgnoredNamespaces);

        return $this->parser->parse($property->getDocComment(), $context);
    }

    public function getPropertyAnnotation(ReflectionProperty $property, $annotationName)
    {
        $annotations = $this->getPropertyAnnotations($property);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    public function getMethodAnnotations(ReflectionMethod $method)
    {
        $class = $method->getDeclaringClass();
        $context = 'method ' . $class->getName() . '::' . $method->getName() . '()';

        $this->parser->setTarget(Target::TARGET_METHOD);
        $this->parser->setImports($this->getMethodImports($method));
        $this->parser->setIgnoredAnnotationNames($this->getIgnoredAnnotationNames($class));
        $this->parser->setIgnoredAnnotationNamespaces(self::$globalIgnoredNamespaces);

        return $this->parser->parse($method->getDocComment(), $context);
    }

    public function getMethodAnnotation(ReflectionMethod $method, $annotationName)
    {
        $annotations = $this->getMethodAnnotations($method);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $annotationName) {
                return $annotation;
            }
        }

        return null;
    }

    /**
     * Returns the ignored annotations for the given class.
     *
     * @return array
     */
    private function getIgnoredAnnotationNames(ReflectionClass $class)
    {
        $name = $class->getName();
        if (isset($this->ignoredAnnotationNames[$name])) {
            return $this->ignoredAnnotationNames[$name];
        }

        $this->collectParsingMetadata($class);

        return $this->ignoredAnnotationNames[$name];
    }

    /**
     * Retrieves imports.
     *
     * @return array
     */
    private function getClassImports(ReflectionClass $class)
    {
        $name = $class->getName();
        if (isset($this->imports[$name])) {
            return $this->imports[$name];
        }

        $this->collectParsingMetadata($class);

        return $this->imports[$name];
    }

    /**
     * Retrieves imports for methods.
     *
     * @return array
     */
    private function getMethodImports(ReflectionMethod $method)
    {
        $class = $method->getDeclaringClass();
        $classImports = $this->getClassImports($class);

        $traitImports = [];

        foreach ($class->getTraits() as $trait) {
            if ($trait->hasMethod($method->getName())
                && $trait->getFileName() === $method->getFileName()
            ) {
                $traitImports = array_merge($traitImports, $this->phpParser->parseClass($trait));
            }
        }

        return array_merge($classImports, $traitImports);
    }

    /**
     * Retrieves imports for properties.
     *
     * @return array
     */
    private function getPropertyImports(ReflectionProperty $property)
    {
        $class = $property->getDeclaringClass();
        $classImports = $this->getClassImports($class);

        $traitImports = [];

        foreach ($class->getTraits() as $trait) {
            if ($trait->hasProperty($property->getName())) {
                $traitImports = array_merge($traitImports, $this->phpParser->parseClass($trait));
            }
        }

        return array_merge($classImports, $traitImports);
    }

    /**
     * Collects parsing metadata for a given class.
     */
    private function collectParsingMetadata(ReflectionClass $class)
    {
        $ignoredAnnotationNames = self::$globalIgnoredNames;
        $annotations = $this->preParser->parse($class->getDocComment(), 'class ' . $class->name);

        foreach ($annotations as $annotation) {
            if ($annotation instanceof IgnoreAnnotation) {
                foreach ($annotation->names as $annot) {
                    $ignoredAnnotationNames[$annot] = true;
                }
            }
        }

        $name = $class->getName();

        $this->imports[$name] = array_merge(
            self::$globalImports,
            $this->phpParser->parseClass($class),
            ['__NAMESPACE__' => $class->getNamespaceName()]
        );

        $this->ignoredAnnotationNames[$name] = $ignoredAnnotationNames;
    }
}
