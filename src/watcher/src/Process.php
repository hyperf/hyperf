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
namespace Hyperf\Watcher;

use Hyperf\Di\Annotation\AnnotationInterface;
use Hyperf\Di\Annotation\AnnotationReader;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Aop\ProxyManager;
use Hyperf\Di\BetterReflectionManager;
use Hyperf\Di\MetadataCollector;
use Hyperf\Utils\Filesystem\Filesystem;
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
     * @var string
     */
    protected $class;

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
     * @var string
     */
    protected $path = BASE_PATH . '/runtime/container/collectors.cache';

    public function __construct(string $file, string $class)
    {
        $this->file = $file;
        $this->class = $class;
        $this->reflection = new BetterReflection();
        $this->reader = new AnnotationReader();
        $this->config = ScanConfig::instance('/');
        $this->filesystem = new Filesystem();
    }

    public function __invoke()
    {
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
        $ref = $this->reflection->classReflector()->reflect($this->class);
        BetterReflectionManager::reflectClass($this->class, $ref);
        $this->collect($this->class, $ref);

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
        $manager = new ProxyManager([], [$this->class => $this->file], BASE_PATH . '/runtime/container/proxy/');
        $ref = new \ReflectionClass($manager);
        $method = $ref->getMethod('generateProxyFiles');
        $method->setAccessible(true);
        $method->invokeArgs($manager, [$this->class => []]);
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
}
