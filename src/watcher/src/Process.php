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
namespace Hyperf\Watcher;

use Hyperf\Di\Annotation\AnnotationInterface;
use Hyperf\Di\Annotation\AnnotationReader;
use Hyperf\Di\Annotation\AspectCollector;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Aop\Ast;
use Hyperf\Di\Aop\ProxyManager;
use Hyperf\Di\MetadataCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\Support\Composer;
use Hyperf\Support\Filesystem\Filesystem;
use Hyperf\Watcher\Ast\Metadata;
use Hyperf\Watcher\Ast\RewriteClassNameVisitor;
use PhpParser\NodeTraverser;
use ReflectionClass;

class Process
{
    protected AnnotationReader $reader;

    protected ScanConfig $config;

    protected Filesystem $filesystem;

    protected Ast $ast;

    protected string $path = BASE_PATH . '/runtime/container/scan.cache';

    public function __construct(protected string $file)
    {
        $this->ast = new Ast();
        $this->config = $this->initScanConfig();
        $this->reader = new AnnotationReader($this->config->getIgnoreAnnotations());
        $this->filesystem = new Filesystem();
    }

    public function __invoke()
    {
        $meta = $this->getMetadata($this->file);
        if ($meta === null) {
            return;
        }
        $class = $meta->toClassName();
        $collectors = $this->config->getCollectors();
        [$data, $proxies, $aspectClasses] = file_exists($this->path) ? unserialize(file_get_contents($this->path)) : [[], [], []];
        foreach ($data as $collector => $deserialized) {
            /** @var MetadataCollector $collector */
            if (in_array($collector, $collectors)) {
                $collector::deserialize($deserialized);
            }
        }

        if (! empty($this->file)) {
            require $this->file;
        }

        // Collect the annotations.
        $ref = ReflectionManager::reflectClass($class);
        foreach ($collectors as $collector) {
            $collector::clear($class);
        }
        $this->collect($class, $ref);

        $collectors = $this->config->getCollectors();
        $data = [];
        /** @var MetadataCollector|string $collector */
        foreach ($collectors as $collector) {
            $data[$collector] = $collector::serialize();
        }

        $composerLoader = Composer::getLoader();
        $composerLoader->addClassMap($this->config->getClassMap());
        $this->deleteAspectClasses($aspectClasses, $proxies, $class);

        // Reload the proxy class.
        $manager = new ProxyManager(array_merge($composerLoader->getClassMap(), $proxies, [$class => $this->file]), BASE_PATH . '/runtime/container/proxy/');
        $proxies = $manager->getProxies();
        $aspectClasses = $manager->getAspectClasses();

        $this->putCache($this->path, serialize([$data, $proxies, $aspectClasses]));
    }

    public function collect($className, ReflectionClass $reflection)
    {
        // Parse class annotations
        $classAnnotations = $this->reader->getClassAnnotations($reflection);
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
            $propertyAnnotations = $this->reader->getPropertyAnnotations($property);
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
            $methodAnnotations = $this->reader->getMethodAnnotations($method);
            if (! empty($methodAnnotations)) {
                foreach ($methodAnnotations as $methodAnnotation) {
                    if ($methodAnnotation instanceof AnnotationInterface) {
                        $methodAnnotation->collectMethod($className, $method->getName());
                    }
                }
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

    protected function getMetadata(string $file): ?Metadata
    {
        $stmts = $this->ast->parse($this->filesystem->get($file));
        $meta = new Metadata();
        $meta->path = $file;
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new RewriteClassNameVisitor($meta));
        $traverser->traverse($stmts);
        if (! $meta->isClass()) {
            return null;
        }
        return $meta;
    }

    protected function initScanConfig(): ScanConfig
    {
        return ScanConfig::instance(BASE_PATH . '/config/');
    }

    protected function deleteAspectClasses($aspectClasses, $proxies, $class): void
    {
        foreach ($aspectClasses as $aspect => $classes) {
            if ($aspect !== $class) {
                continue;
            }
            foreach ($classes as $path) {
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }

        $classesAspects = AspectCollector::get('classes', []);
        foreach ($classesAspects as $aspect => $rules) {
            if ($aspect !== $class) {
                continue;
            }
            foreach ($rules as $rule) {
                if (isset($proxies[$rule]) && file_exists($proxies[$rule])) {
                    unlink($proxies[$rule]);
                }
            }
        }
    }
}
