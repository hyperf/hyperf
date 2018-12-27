<?php

namespace Hyperflex\Di\Definition;

use Hyperflex\Di\Annotation\AnnotationCollector;
use Hyperflex\Di\Annotation\AspectCollector;
use Hyperflex\Di\Annotation\Inject;
use Hyperflex\Di\Annotation\Scanner;
use Hyperflex\Di\ReflectionManager;
use ReflectionFunctionAbstract;
use Symfony\Component\Finder\Finder;
use function class_exists;
use function count;
use function explode;
use function fclose;
use function feof;
use function fgets;
use function file_exists;
use function file_put_contents;
use function filemtime;
use function fopen;
use function implode;
use function interface_exists;
use function is_array;
use function is_callable;
use function is_dir;
use function is_readable;
use function is_string;
use function md5;
use function method_exists;
use function print_r;
use function trim;
use function array_key_exists;
use function preg_match;
use function str_replace;

class DefinitionSource implements DefinitionSourceInterface
{

    /**
     * Path of annotation meta data cache
     *
     * @var string
     */
    private $cachePath = BASE_PATH . '/runtime/container/annotations.cache';

    /**
     * @var array
     */
    private $source;

    /**
     * @var Scanner
     */
    private $scanner;

    public function __construct(array $source, array $scanDir, Scanner $scanner)
    {
        // Format relative paths into absolute paths
        $scanDir = array_map(function ($value) {
            return BASE_PATH . '/' . $value;
        }, $scanDir);

        $this->scanner = $scanner;
        // Scan the specified paths and collect the ast and annotations.
        $this->scan($scanDir);
        $this->source = $this->normalizeSource($source);
    }

    /**
     * Returns the DI definition for the entry name.
     *
     * @return DefinitionInterface|null
     */
    public function getDefinition(string $name): ?DefinitionInterface
    {
        if (! isset($this->source[$name])) {
            $this->source[$name] = $this->autowire($name);
        }
        return $this->source[$name];
    }

    /**
     * @return array Definitions indexed by their name.
     */
    public function getDefinitions(): array
    {
        return $this->source;
    }

    public function addDefinition(string $name, array $definition): self
    {
        $this->source[$name] = $definition;
        return $this;
    }

    public function clearDefinitions(): void
    {
        $this->source = [];
    }

    /**
     * Read the type-hinting from the parameters of the function.
     */
    private function getParametersDefinition(ReflectionFunctionAbstract $constructor): array
    {
        $parameters = [];

        foreach ($constructor->getParameters() as $index => $parameter) {
            // Skip optional parameters
            if ($parameter->isOptional()) {
                continue;
            }

            $parameterClass = $parameter->getClass();

            if ($parameterClass) {
                $parameters[$index] = new Reference($parameterClass->getName());
            }
        }

        return $parameters;
    }

    /**
     * Normaliaze the user definition source to a standard definition souce.
     */
    private function normalizeSource(array $source): array
    {
        $definitions = [];
        foreach ($source as $identifier => $definition) {
            if (is_string($definition) && class_exists($definition)) {
                if (method_exists($definition, '__invoke')) {
                    $definitions[$identifier] = new FactoryDefinition($identifier, $definition, []);
                } else {
                    $definitions[$identifier] = $this->autowire($identifier, new ObjectDefinition($identifier, $definition));
                }
            } elseif (is_array($definition) && is_callable($definition)) {
                $definitions[$identifier] = new FactoryDefinition($identifier, $definition, []);
            }
        }
        return $definitions;
    }

    private function autowire(string $name, ObjectDefinition $definition = null): ObjectDefinition
    {
        $className = $definition ? $definition->getClassName() : $name;
        if (! class_exists($className) && ! interface_exists($className)) {
            return $definition;
        }

        $definition = $definition ? : new ObjectDefinition($name);

        // Constructor
        $class = ReflectionManager::reflectClass($className);
        $constructor = $class->getConstructor();
        if ($constructor && $constructor->isPublic()) {
            $constructorInjection = new MethodInjection('__construct', $this->getParametersDefinition($constructor));
            $definition->completeConstructorInjection($constructorInjection);
        }

        // Properties
        $propertiesMetadata = AnnotationCollector::get($className);
        if (isset($propertiesMetadata['_p'])) {
            foreach ($propertiesMetadata['_p'] as $propertyName => $value) {
                if (! isset($value[Inject::class])) {
                    continue;
                }
                $propertyInjection = new PropertyInjection($propertyName, new Reference($value[Inject::class]));
                $definition->addPropertyInjection($propertyInjection);
            }
        }

        $definition->setNeedProxy($this->isNeedProxy($class));

        return $definition;
    }

    private function scan(array $paths): bool
    {
        $pathsHash = md5(implode(',', $paths));
        if ($this->hasAvailableCache($paths, $pathsHash, $this->cachePath)) {
            $this->printLn('Detected an available cache, skip the scan process.');
            list(, $annotationMetadata, $aspectMetadata) = explode(PHP_EOL, file_get_contents($this->cachePath));
            // Deserialize metadata when the cache is valid 
            AnnotationCollector::deserialize($annotationMetadata);
            AspectCollector::deserialize($aspectMetadata);
            return false;
        }
        $this->printLn('Scanning ...');
        $this->scanner->scan($paths);
        if (! file_exists($this->cachePath)) {
            $exploded = explode('/', $this->cachePath);
            unset($exploded[count($exploded) - 1]);
            $dirPath = implode('/', $exploded);
            if (! is_dir($dirPath)) {
                mkdir($dirPath, 0755, true);
            }
        }
        $data = implode(PHP_EOL, [$pathsHash, AnnotationCollector::serialize(), AspectCollector::serialize()]);
        file_put_contents($this->cachePath, $data);
        $this->printLn('Scan completed.');
        return true;
    }

    private function hasAvailableCache(array $paths, string $pathsHash, string $filename): bool
    {
        if (! file_exists($filename) || ! is_readable($filename)) {
            return false;
        }
        $handler = fopen($filename, 'r');
        while (! feof($handler)) {
            $line = fgets($handler);
            if (trim($line) !== $pathsHash) {
                return false;
            }
            break;
        }
        fclose($handler);
        $cacheLastModified = filemtime($filename) ?? 0;
        $finder = new Finder();
        $finder->files()->in($paths)->name('*.php');
        foreach ($finder as $file) {
            if ($file->getMTime() > $cacheLastModified) {
                return false;
            }
        }
        return true;
    }

    private function printLn(string $message): void
    {
        print_r($message . PHP_EOL);
    }

    private function isNeedProxy(\ReflectionClass $class)
    {
        $className = $class->getName();
        $aspect = AspectCollector::get('class.static');
        if (array_key_exists($className, $aspect)) {
            return true;
        }

        $aspect = AspectCollector::get('class.dynamic');
        foreach ($aspect as $preg => $aspects) {
            $preg = str_replace(['*', '\\'], ['.*', '\\\\'], $preg);
            $pattern = "/^$preg$/";

            if (preg_match($pattern, $className)) {
                AspectCollector::collectClassAndAnnotation($aspects, [$className], []);
                return true;
            }
        }

        // TODO: Check class whether to contain the annotations.

        return false;
    }

}