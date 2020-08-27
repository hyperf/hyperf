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

use Hyperf\Config\ProviderConfig;
use Hyperf\Di\BetterReflectionManager;
use Hyperf\Di\ClassLoader;
use Hyperf\Di\Exception\DirectoryNotExistException;
use Hyperf\Di\MetadataCollector;
use Hyperf\Utils\Filesystem\Filesystem;
use ReflectionProperty;
use Roave\BetterReflection\Reflection\Adapter;
use Roave\BetterReflection\Reflection\ReflectionClass;

class Scanner
{
    /**
     * @var \Hyperf\Di\ClassLoader
     */
    protected $classloader;

    /**
     * @var ScanConfig
     */
    protected $scanConfig;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $path = BASE_PATH . '/runtime/container/collectors.cache';

    public function __construct(ClassLoader $classloader, ScanConfig $scanConfig)
    {
        $this->classloader = $classloader;
        $this->scanConfig = $scanConfig;
        $this->filesystem = new Filesystem();

        foreach ($scanConfig->getIgnoreAnnotations() as $annotation) {
            AnnotationReader::addGlobalIgnoredName($annotation);
        }
        foreach ($scanConfig->getGlobalImports() as $alias => $annotation) {
            AnnotationReader::addGlobalImports($alias, $annotation);
        }
    }

    public function collect(AnnotationReader $reader, ReflectionClass $reflection)
    {
        $className = $reflection->getName();
        if ($path = $this->scanConfig->getClassMap()[$className] ?? null) {
            if ($reflection->getFileName() !== $path) {
                // When the original class is dynamically replaced, the original class should not be collected.
                return;
            }
        }
        // Parse class annotations
        $classAnnotations = $reader->getClassAnnotations(new Adapter\ReflectionClass($reflection));
        if (! empty($classAnnotations)) {
            foreach ($classAnnotations as $classAnnotation) {
                if ($classAnnotation instanceof AnnotationInterface) {
                    $classAnnotation->collectClass($className);
                }
            }
        }
        // Parse properties annotations
        $properties = $reflection->getImmediateProperties();
        foreach ($properties as $property) {
            $propertyAnnotations = $reader->getPropertyAnnotations(new Adapter\ReflectionProperty($property));
            if (! empty($propertyAnnotations)) {
                foreach ($propertyAnnotations as $propertyAnnotation) {
                    if ($propertyAnnotation instanceof AnnotationInterface) {
                        $propertyAnnotation->collectProperty($className, $property->getName());
                    }
                }
            }
        }
        // Parse methods annotations
        $methods = $reflection->getImmediateMethods();
        foreach ($methods as $method) {
            $methodAnnotations = $reader->getMethodAnnotations(new Adapter\ReflectionMethod($method));
            if (! empty($methodAnnotations)) {
                foreach ($methodAnnotations as $methodAnnotation) {
                    if ($methodAnnotation instanceof AnnotationInterface) {
                        $methodAnnotation->collectMethod($className, $method->getName());
                    }
                }
            }
        }

        unset($reflection, $classAnnotations, $properties, $methods);
    }

    /**
     * @return ReflectionClass[]
     */
    public function scan(): array
    {
        $paths = $this->scanConfig->getPaths();
        $collectors = $this->scanConfig->getCollectors();
        $classes = [];
        if (! $paths) {
            return $classes;
        }

        $annotationReader = new AnnotationReader();
        $lastCacheModified = $this->deserializeCachedCollectors($collectors);
        // TODO: The online mode won't init BetterReflectionManager when has cache.
        if ($lastCacheModified > 0 && $this->scanConfig->isCacheable()) {
            return [];
        }

        $paths = $this->normalizeDir($paths);

        $reflector = BetterReflectionManager::initClassReflector($paths);
        $classes = $reflector->getAllClasses();
        // Initialize cache for BetterReflectionManager.
        foreach ($classes as $class) {
            BetterReflectionManager::reflectClass($class->getName(), $class);
        }

        $this->clearRemovedClasses($collectors, $classes);

        foreach ($classes as $reflectionClass) {
            if ($this->filesystem->lastModified($reflectionClass->getFileName()) >= $lastCacheModified) {
                /** @var MetadataCollector $collector */
                foreach ($collectors as $collector) {
                    $collector::clear($reflectionClass->getName());
                }

                $this->collect($annotationReader, $reflectionClass);
            }
        }

        $this->loadAspects($lastCacheModified);

        $data = [];
        /** @var MetadataCollector|string $collector */
        foreach ($collectors as $collector) {
            $data[$collector] = $collector::serialize();
        }

        if ($data) {
            $this->putCache($this->path, serialize($data));
        }

        unset($annotationReader);

        return $classes;
    }

    /**
     * Normalizes given directory names by removing directory not exist.
     * @throws DirectoryNotExistException
     */
    public function normalizeDir(array $paths): array
    {
        $result = [];
        foreach ($paths as $path) {
            if (is_dir($path)) {
                $result[] = $path;
            }
        }

        if ($paths && ! $result) {
            throw new DirectoryNotExistException('The scanned directory does not exist');
        }

        return $result;
    }

    protected function deserializeCachedCollectors(array $collectors): int
    {
        if (! file_exists($this->path)) {
            return 0;
        }

        $data = unserialize(file_get_contents($this->path));
        foreach ($data as $collector => $deserialized) {
            /** @var MetadataCollector $collector */
            if (in_array($collector, $collectors)) {
                $collector::deserialize($deserialized);
            }
        }

        return $this->filesystem->lastModified($this->path);
    }

    /**
     * @param ReflectionClass[] $reflections
     */
    protected function clearRemovedClasses(array $collectors, array $reflections): void
    {
        $path = BASE_PATH . '/runtime/container/classes.cache';
        $classes = [];
        foreach ($reflections as $reflection) {
            $classes[] = $reflection->getName();
        }

        $data = [];
        if ($this->filesystem->exists($path)) {
            $data = unserialize($this->filesystem->get($path));
        }

        $this->putCache($path, serialize($classes));

        $removed = array_diff($data, $classes);

        foreach ($removed as $class) {
            /** @var MetadataCollector $collector */
            foreach ($collectors as $collector) {
                $collector::clear($class);
            }
        }
    }

    protected function putCache($path, $data)
    {
        if (! $this->filesystem->isDirectory($dir = dirname($path))) {
            $this->filesystem->makeDirectory($dir, 0755, true);
        }

        $this->filesystem->put($path, $data);
    }

    /**
     * Load aspects to AspectCollector by configuration files and ConfigProvider.
     */
    protected function loadAspects(int $lastCacheModified): void
    {
        $configDir = $this->scanConfig->getConfigDir();
        if (! $configDir) {
            return;
        }

        $aspectsPath = $configDir . '/autoload/aspects.php';
        $basePath = $configDir . '/config.php';
        $aspects = file_exists($aspectsPath) ? include $aspectsPath : [];
        $baseConfig = file_exists($basePath) ? include $basePath : [];
        $providerConfig = [];
        if (class_exists(ProviderConfig::class)) {
            $providerConfig = ProviderConfig::load();
        }
        if (! isset($aspects) || ! is_array($aspects)) {
            $aspects = [];
        }
        if (! isset($baseConfig['aspects']) || ! is_array($baseConfig['aspects'])) {
            $baseConfig['aspects'] = [];
        }
        if (! isset($providerConfig['aspects']) || ! is_array($providerConfig['aspects'])) {
            $providerConfig['aspects'] = [];
        }
        $aspects = array_merge($providerConfig['aspects'], $baseConfig['aspects'], $aspects);

        [$removed, $changed] = $this->getChangedAspects($aspects, $lastCacheModified);
        // When the aspect removed from config, it should removed from AspectCollector.
        foreach ($removed as $aspect) {
            AspectCollector::clear($aspect);
        }

        foreach ($aspects ?? [] as $key => $value) {
            if (is_numeric($key)) {
                $aspect = $value;
                $priority = null;
            } else {
                $aspect = $key;
                $priority = (int) $value;
            }

            if (! in_array($aspect, $changed)) {
                continue;
            }

            // Create the aspect instance without invoking their constructor.
            $reflectionClass = BetterReflectionManager::reflectClass($aspect);
            $properties = $reflectionClass->getImmediateProperties(ReflectionProperty::IS_PUBLIC);
            $instanceClasses = $instanceAnnotations = [];
            $instancePriority = null;
            foreach ($properties as $property) {
                if ($property->getName() === 'classes') {
                    $instanceClasses = $property->getDefaultValue();
                } elseif ($property->getName() === 'annotations') {
                    $instanceAnnotations = $property->getDefaultValue();
                } elseif ($property->getName() === 'priority') {
                    $instancePriority = $property->getDefaultValue();
                }
            }

            $classes = $instanceClasses ?: [];
            // Annotations
            $annotations = $instanceAnnotations ?: [];
            // Priority
            $priority = $priority ?: ($instancePriority ?? null);
            // Save the metadata to AspectCollector
            AspectCollector::setAround($aspect, $classes, $annotations, $priority);
        }
    }

    protected function getChangedAspects(array $aspects, int $lastCacheModified): array
    {
        $path = BASE_PATH . '/runtime/container/aspects.cache';
        $classes = [];
        foreach ($aspects as $key => $value) {
            if (is_numeric($key)) {
                $classes[] = $value;
            } else {
                $classes[] = $key;
            }
        }

        $data = [];
        if ($this->filesystem->exists($path)) {
            $data = unserialize($this->filesystem->get($path));
        }

        $this->putCache($path, serialize($classes));

        $diff = array_diff($data, $classes);
        $changed = array_diff($classes, $data);
        $removed = [];
        foreach ($diff as $item) {
            $annotation = AnnotationCollector::getClassAnnotation($item, Aspect::class);
            if (is_null($annotation)) {
                $removed[] = $item;
            }
        }
        foreach ($classes as $class) {
            $file = $this->classloader->getComposerClassLoader()->findFile($class);
            if ($lastCacheModified <= $this->filesystem->lastModified($file)) {
                $changed[] = $class;
            }
        }

        return [
            array_values(array_unique($removed)),
            array_values(array_unique($changed)),
        ];
    }
}
