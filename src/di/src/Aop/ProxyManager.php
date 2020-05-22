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

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AspectCollector;
use Hyperf\Di\BetterReflectionManager;
use Hyperf\Utils\Filesystem\Filesystem;
use Roave\BetterReflection\Reflection\ReflectionClass;

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

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(
        array $reflectionClassMap = [],
        array $composerLoaderClassMap = [],
        string $proxyDir = ''
    ) {
        $this->proxyDir = $proxyDir;
        $this->filesystem = new Filesystem();
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
            mkdir($this->getProxyDir(), 0755, true);
        }
        // WARNING: Ast class SHOULD NOT use static instance, because it will read  the code from file, then would be caused coroutine switch.
        $ast = new Ast();
        foreach ($proxies as $className => $aspects) {
            $proxyFiles[$className] = $this->putProxyFile($ast, $className);
        }
        return $proxyFiles;
    }

    protected function putProxyFile(Ast $ast, $className)
    {
        $proxyFilePath = $this->getProxyFilePath($className);
        $modified = true;
        if (file_exists($proxyFilePath)) {
            $modified = $this->isModified($className, $proxyFilePath);
        }

        if ($modified) {
            $code = $ast->proxy($className);
            file_put_contents($proxyFilePath, $code);
        }

        return $proxyFilePath;
    }

    protected function isModified(string $className, string $proxyFilePath = null): bool
    {
        $proxyFilePath = $proxyFilePath ?? $this->getProxyFilePath($className);
        $time = $this->filesystem->lastModified($proxyFilePath);
        $origin = BetterReflectionManager::reflectClass($className);
        if ($time > $this->filesystem->lastModified($origin->getFileName())) {
            return false;
        }

        return true;
    }

    protected function getProxyFilePath($className)
    {
        return $this->getProxyDir() . str_replace('\\', '_', $className) . '.proxy.php';
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

    /**
     * @param ReflectionClass[] $reflectionClassMap
     */
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
            $classAnnotations = $this->retrieveAnnotations($className . '._c');
            // Aggregate all methods annotations
            $methodAnnotations = $this->retrieveAnnotations($className . '._m');
            // Aggregate all properties annotations
            $propertyAnnotations = $this->retrieveAnnotations($className . '._p');
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

    protected function retrieveAnnotations(string $annotationCollectorKey): array
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
