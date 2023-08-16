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
use Hyperf\Di\Aop\ProxyManager;
use Hyperf\Di\Exception\DirectoryNotExistException;
use Hyperf\Di\MetadataCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\Di\ScanHandler\ScanHandlerInterface;
use Hyperf\Support\Composer;
use Hyperf\Support\Filesystem\Filesystem;
use ReflectionClass;

class Scanner
{
    protected Filesystem $filesystem;

    protected string $path = BASE_PATH . '/runtime/container/scan.cache';

    public function __construct(protected ScanConfig $scanConfig, protected ScanHandlerInterface $handler)
    {
        $this->filesystem = new Filesystem();
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
        $classAnnotations = $reader->getClassAnnotations($reflection);
        if (! empty($classAnnotations)) {
            foreach ($classAnnotations as $classAnnotation) {
                if ($classAnnotation instanceof AnnotationInterface) {
                    $classAnnotation->collectClass($className);
                }
            }
        }
        // Parse properties annotations
        $properties = $reflection->getProperties();
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
        // Parse methods annotations
        $methods = $reflection->getMethods();
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

        unset($reflection, $classAnnotations, $properties, $methods);
    }

    public function scan(array $classMap = [], string $proxyDir = ''): array
    {
        $paths = $this->scanConfig->getPaths();
        $collectors = $this->scanConfig->getCollectors();
        if (! $paths) {
            return [];
        }

        $lastCacheModified = file_exists($this->path) ? $this->filesystem->lastModified($this->path) : 0;
        if ($lastCacheModified > 0 && $this->scanConfig->isCacheable()) {
            return $this->deserializeCachedScanData($collectors);
        }

        $scanned = $this->handler->scan();
        if ($scanned->isScanned()) {
            return $this->deserializeCachedScanData($collectors);
        }

        $this->deserializeCachedScanData($collectors);

        $annotationReader = new AnnotationReader($this->scanConfig->getIgnoreAnnotations());

        $paths = $this->normalizeDir($paths);

        $classes = ReflectionManager::getAllClasses($paths);

        $this->clearRemovedClasses($collectors, $classes);

        $reflectionClassMap = [];
        foreach ($classes as $className => $reflectionClass) {
            $reflectionClassMap[$className] = $reflectionClass->getFileName();
            if ($this->filesystem->lastModified($reflectionClass->getFileName()) >= $lastCacheModified) {
                /** @var MetadataCollector $collector */
                foreach ($collectors as $collector) {
                    $collector::clear($className);
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

        // Get the class map of Composer loader
        $classMap = array_merge($reflectionClassMap, $classMap);
        $proxyManager = new ProxyManager($classMap, $proxyDir);
        $proxies = $proxyManager->getProxies();
        $aspectClasses = $proxyManager->getAspectClasses();

        $this->putCache($this->path, serialize([$data, $proxies, $aspectClasses]));
        exit;
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

    protected function deserializeCachedScanData(array $collectors): array
    {
        if (! file_exists($this->path)) {
            return [];
        }

        [$data, $proxies] = unserialize(file_get_contents($this->path));
        foreach ($data as $collector => $deserialized) {
            /** @var MetadataCollector $collector */
            if (in_array($collector, $collectors)) {
                $collector::deserialize($deserialized);
            }
        }

        return $proxies;
    }

    /**
     * @param ReflectionClass[] $reflections
     */
    protected function clearRemovedClasses(array $collectors, array $reflections): void
    {
        $path = BASE_PATH . '/runtime/container/classes.cache';
        $classes = array_keys($reflections);

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

    protected function putCache(string $path, $data)
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
        // When the aspect removed from config, it should be removed from AspectCollector.
        foreach ($removed as $aspect) {
            AspectCollector::clear($aspect);
        }

        foreach ($aspects as $key => $value) {
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

            [$instanceClasses, $instanceAnnotations, $instancePriority] = AspectLoader::load($aspect);

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
            $file = Composer::getLoader()->findFile($class);
            if ($file === false) {
                echo sprintf('Skip class %s, because it does not exist in composer class loader.', $class) . PHP_EOL;
                continue;
            }
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
