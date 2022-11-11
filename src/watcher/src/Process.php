<?php

declare(strict_types=1);
/**
 * This file is part of tw591ms/im.
 *
 * @link     https://code.addcn.com/tw591ms/im
 * @document https://code.addcn.com/tw591ms/im
 * @contact  hdj@addcn.com
 */
namespace Hyperf\Watcher;

use Hyperf\Di\Annotation\AnnotationInterface;
use Hyperf\Di\Annotation\AnnotationReader;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Aop\Ast;
use Hyperf\Di\Aop\ProxyManager;
use Hyperf\Di\MetadataCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\Utils\Filesystem\Filesystem;
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

    protected string $path;

    protected Option $option;

    public function __construct(protected string $file, protected string $configFile = '.watcher.php')
    {
        $options = (array) include dirname(__DIR__, 2) . '/publish/watcher.php';

        if (file_exists($configFile)) {
            $options = array_replace($options, (array) include $configFile);
            $options['config_file'] = $configFile;
        }

        /* @var Option */
        $this->option = make(Option::class, compact('options'));
        $this->path = $this->option->path('runtime/container/scan.cache');

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
        [$data, $proxies] = file_exists($this->path) ? unserialize(file_get_contents($this->path)) : [[], []];
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

        // Reload the proxy class.
        $manager = new ProxyManager(array_merge($proxies, [$class => $this->file]), $this->option->path('/runtime/container/proxy/'));
        $proxies = $manager->getProxies();
        $this->putCache($this->path, serialize([$data, $proxies]));
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
        return ScanConfig::instance($this->option->path('config'));
    }
}
