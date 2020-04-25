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

namespace Hyperf\Autoload;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AspectCollector;
use Roave\BetterReflection\Reflection\ReflectionClass;

class ProxyManager
{
    /**
     * The class names which be scaned.
     *
     * @var array
     */
    protected $classNames = [];

    /**
     * The classes which be rewrited by proxy.
     *
     * @var array
     */
    protected $proxies = [];

    public function __construct(array $reflectionClassMap = [], array $composerLoaderClassMap = [])
    {
        $this->proxies = array_merge(
            $this->generateProxyFiles($this->initProxiesByReflectionClassMap($reflectionClassMap)),
            $this->generateProxyFiles($this->initProxiesByComposerClassMap($composerLoaderClassMap))
        );
    }

    public function getProxies(): array
    {
        return $this->proxies;
    }

    public function isScaned(string $class): bool
    {
        return in_array($class, $this->classNames);
    }

    protected function initProxiesByComposerClassMap(array $classMap)
    {
        $classes = [];
        $classAspects = value(function () {
            $aspects = AspectCollector::get('classes', []);
            // Remove the useless aspect rules
            foreach ($aspects as $aspect => $rules) {
                if (! $rules) {
                    unset($aspects[$aspect]);
                }
            }
            return $aspects;
        });
        if ($classAspects) {
            foreach ($classMap as $className => $file) {
                $match = [];
                foreach ($classAspects as $aspect => $rules) {
                    foreach ($rules as $rule) {
                        if (ProxyManager::isMatch($rule, $className)) {
                            $match[] = $aspect;
                        }
                    }
                }
                if ($match) {
                    $match = array_flip(array_flip($match));
                    $classes[$className] = $match;
                }
            }
        }

        return $this->generateProxyFiles($classes);
    }

    protected function generateProxyFiles(array $proxies = []): array
    {
        $proxyFiles = [];
        $proxyFileDir = BASE_PATH . '/runtime/container/proxy/';
        if (! file_exists($proxyFileDir)) {
            mkdir($proxyFileDir, 0755, true);
        }
        // WARNING: Ast should not use static instance. Because it will read code from file, it can be caused coroutine switch.
        $ast = new Ast();
        foreach ($proxies as $className => $aspects) {
            $code = $ast->proxy($className, $className);
            $proxyFilePath = $proxyFileDir . str_replace('\\', '_', $className) . '_' . crc32($code) . '.php';
            if (! file_exists($proxyFilePath)) {
                file_put_contents($proxyFilePath, $code);
            }
            $proxyFiles[$className] = $proxyFilePath;
        }
        return $proxyFiles;
    }

    protected static function isMatch(string $rule, string $target): bool
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

    protected function initProxiesByReflectionClassMap(array $betterReflectionClasses = []): array
    {
        // According to the data of AspectCollector to parse all the classes that need proxy.
        $proxies = [];
        $classesAspects = AspectCollector::get('classes', []);
        foreach ($classesAspects as $aspect => $rules) {
            foreach ($rules as $rule) {
                foreach ($betterReflectionClasses as $class) {
                    if ($class instanceof ReflectionClass || ! static::isMatch($rule, $class->getName())) {
                        continue;
                    }
                    $proxies[$class->getName()][] = $aspect;
                }
            }
        }

        foreach ($betterReflectionClasses as $class) {
            $className = $class->getName();
            $this->classNames[] = $className;
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
                            if (static::isMatch($rule, $annotation)) {
                                $proxies[$className][] = $aspect;
                            }
                        }
                    }
                }
            }
        }
        return $proxies;
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

}
