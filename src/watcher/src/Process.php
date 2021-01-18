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
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Aop\Ast;
use Hyperf\Di\Aop\ProxyManager;
use Hyperf\Di\BetterReflectionManager;
use Hyperf\Di\MetadataCollector;
use Hyperf\Utils\Filesystem\Filesystem;
use Hyperf\Watcher\Ast\Metadata;
use Hyperf\Watcher\Ast\RewriteClassNameVisitor;
use PhpParser\NodeTraverser;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\Adapter;
use Roave\BetterReflection\Reflection\ReflectionClass;

class Process
{
    /**
     * @var string
     */
    protected $file;

    /**
     * @var BetterReflection
     */
    protected $reflection;

    /**
     * @var AnnotationReader
     */
    protected $reader;

    /**
     * @var ScanConfig
     */
    protected $config;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Ast
     */
    protected $ast;

    /**
     * @var string
     */
    protected $path = BASE_PATH . '/runtime/container/collectors.cache';

    public function __construct(string $file)
    {
        $this->file = $file;
        $this->ast = new Ast();
        $this->reflection = new BetterReflection();
        $this->config = $this->initScanConfig();
        $this->reader = new AnnotationReader();
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
        $data = unserialize(file_get_contents($this->path));
        foreach ($data as $collector => $deserialized) {
            /** @var MetadataCollector $collector */
            if (in_array($collector, $collectors)) {
                $collector::deserialize($deserialized);
            }
        }

        require $this->file;

        // Collect the annotations.
        $ref = $this->reflection->classReflector()->reflect($class);
        BetterReflectionManager::reflectClass($class, $ref);
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

        if ($data) {
            $this->putCache($this->path, serialize($data));
        }

        // Reload the proxy class.
        $manager = new ProxyManager([], [$class => $this->file], BASE_PATH . '/runtime/container/proxy/');
        $ref = new \ReflectionClass($manager);
        $method = $ref->getMethod('generateProxyFiles');
        $method->setAccessible(true);
        $method->invokeArgs($manager, [$class => []]);
    }

    public function collect($className, ReflectionClass $reflection)
    {
        // Parse class annotations
        $classAnnotations = $this->reader->getClassAnnotations(new Adapter\ReflectionClass($reflection));
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
            $propertyAnnotations = $this->reader->getPropertyAnnotations(new Adapter\ReflectionProperty($property));
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
            $methodAnnotations = $this->reader->getMethodAnnotations(new Adapter\ReflectionMethod($method));
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
        $config = ScanConfig::instance(BASE_PATH . '/config/');
        foreach ($config->getIgnoreAnnotations() as $annotation) {
            AnnotationReader::addGlobalIgnoredName($annotation);
        }
        foreach ($config->getGlobalImports() as $alias => $annotation) {
            AnnotationReader::addGlobalImports($alias, $annotation);
        }
        return $config;
    }
}
