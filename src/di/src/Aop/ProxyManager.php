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
namespace Hyperf\Di\Aop;

use Doctrine\Instantiator\Instantiator;
use Hyperf\Config\ProviderConfig;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AspectCollector;

class ProxyManager
{
    /**
     * The map to collect the class names which has been handled.
     *
     * @var array
     */
    protected $classNameMap = [];

    /**
     * The classes which be rewrited by proxy.
     *
     * @var array
     */
    protected $proxies = [];

    /**
     * The directory which the proxy file places in.
     *
     * @var string
     */
    protected $proxyDir;

    public function __construct(
        array $reflectionClassMap = [],
        array $composerLoaderClassMap = [],
        string $proxyDir = '',
        string $configDir = ''
    ) {
        $this->proxyDir = $proxyDir;
        $this->loadAspects($configDir);
        $reflectionClassMap && $reflectionClassProxies = $this->generateProxyFiles($this->initProxiesByReflectionClassMap($reflectionClassMap));
        $composerLoaderClassMap && $composerLoaderProxies = $this->generateProxyFiles($this->initProxiesByComposerClassMap($composerLoaderClassMap));
        $this->proxies = array_merge($reflectionClassProxies, $composerLoaderProxies);
    }

    public function getProxies(): array
    {
        return $this->proxies;
    }

    public function getProxyDir(): string
    {
        return $this->proxyDir;
    }

    public function getClassNameMap(): array
    {
        return $this->classNameMap;
    }

    public function isClassHandled(string $className): bool
    {
        return in_array($className, $this->getClassNameMap());
    }

    protected function generateProxyFiles(array $proxies = []): array
    {
        $proxyFiles = [];
        if (! $proxies) {
            return $proxyFiles;
        }
        if (! file_exists($this->getProxyDir())) {
            mkdir($this->getProxyFileDir(), 0755, true);
        }
        // WARNING: Ast class SHOULD NOT use static instance, because it will read  the code from file, then would be caused coroutine switch.
        $ast = new Ast();
        foreach ($proxies as $className => $aspects) {
            $code = $ast->proxy($className);
            $proxyFilePath = $this->getProxyDir() . str_replace('\\', '_', $className) . '_' . crc32($code) . '.php';
            if (! file_exists($proxyFilePath)) {
                file_put_contents($proxyFilePath, $code);
            }
            $proxyFiles[$className] = $proxyFilePath;
        }
        return $proxyFiles;
    }

    protected function isMatch(string $rule, string $target): bool
    {
        if (strpos($rule, '::') !== false) {
            [$rule,] = explode('::', $rule);
        }
        if (strpos($rule, '*') === false && $rule === $target) {
            return true;
        }
        $preg = str_replace(['*', '\\'], ['.*', '\\\\'], $rule);
        $pattern = "/^{$preg}$/";

        if (preg_match($pattern, $target)) {
            return true;
        }

        return false;
    }

    protected function initProxiesByReflectionClassMap(array $reflectionClassMap = []): array
    {
        // According to the data of AspectCollector to parse all the classes that need proxy.
        $proxies = [];
        if (! $reflectionClassMap) {
            return $proxies;
        }
        $classesAspects = AspectCollector::get('classes', []);
        foreach ($classesAspects as $aspect => $rules) {
            foreach ($rules as $rule) {
                foreach ($reflectionClassMap as $class) {
                    if (! $this->isMatch($rule, $class->getName())) {
                        continue;
                    }
                    $proxies[$class->getName()][] = $aspect;
                }
            }
        }

        foreach ($reflectionClassMap as $class) {
            $className = $class->getName();
            $this->classNameMap[] = $className;
            // Aggregate the class annotations
            $classAnnotations = $this->retriveAnnotations($className . '._c');
            // Aggregate all methods annotations
            $methodAnnotations = $this->retriveAnnotations($className . '._m');
            // Aggregate all properties annotations
            $propertyAnnotations = $this->retriveAnnotations($className . '._p');
            $annotations = array_unique(array_merge($classAnnotations, $methodAnnotations, $propertyAnnotations));
            if ($annotations) {
                $annotationsAspects = AspectCollector::get('annotations', []);
                foreach ($annotationsAspects as $aspect => $rules) {
                    foreach ($rules as $rule) {
                        foreach ($annotations as $annotation) {
                            if ($this->isMatch($rule, $annotation)) {
                                $proxies[$className][] = $aspect;
                            }
                        }
                    }
                }
            }
        }
        return $proxies;
    }

    protected function initProxiesByComposerClassMap(array $classMap = []): array
    {
        $proxies = [];
        if (! $classMap) {
            return $proxies;
        }
        $classAspects = $this->getClassAspects();
        if ($classAspects) {
            foreach ($classMap as $className => $file) {
                $match = [];
                $this->classNameMap[] = $className;
                foreach ($classAspects as $aspect => $rules) {
                    foreach ($rules as $rule) {
                        if ($this->isMatch($rule, $className)) {
                            $match[] = $aspect;
                        }
                    }
                }
                if ($match) {
                    $match = array_flip(array_flip($match));
                    $proxies[$className] = $match;
                }
            }
        }

        return $proxies;
    }

    protected function getClassAspects(): array
    {
        $aspects = AspectCollector::get('classes', []);
        // Remove the useless aspect rules
        foreach ($aspects as $aspect => $rules) {
            if (! $rules) {
                unset($aspects[$aspect]);
            }
        }
        return $aspects;
    }

    protected function retriveAnnotations(string $annotationCollectorKey): array
    {
        $defined = [];
        $annotations = AnnotationCollector::get($annotationCollectorKey, []);
        foreach ($annotations as $k => $annotation) {
            if (is_object($annotation)) {
                $defined[] = $k;
            } else {
                $defined = array_merge($defined, array_keys($annotation));
            }
        }
        return $defined;
    }

    /**
     * Load aspects to AspectCollector by configuration files and ConfigProvider.
     */
    protected function loadAspects(string $configDir): void
    {
        if (! $configDir) {
            return;
        }
        $aspects = require $configDir . 'autoload/aspects.php';
        $baseConfig = require $configDir . 'config.php';
        $providerConfig = ProviderConfig::load();
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

        foreach ($aspects ?? [] as $key => $value) {
            if (is_numeric($key)) {
                $aspect = $value;
                $priority = null;
            } else {
                $aspect = $key;
                $priority = (int) $value;
            }
            // Create the aspect instance without invoking their constructor.
            $instantitor = new Instantiator();
            $instance = $instantitor->instantiate($aspect);
            switch ($instance) {
                case $instance instanceof AroundInterface:
                    $classes = property_exists($instance, 'classes') ? $instance->classes : [];
                    // Annotations
                    $annotations = property_exists($instance, 'annotations') ? $instance->annotations : [];
                    // Priority
                    $priority = property_exists($instance, 'priority') ? $instance->priority : null;
                    // Save the metadata to AspectCollector
                    AspectCollector::setAround($aspect, $classes, $annotations, $priority);
                    break;
            }
        }
    }
}
