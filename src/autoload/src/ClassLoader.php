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

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AspectCollector;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Composer;

class ClassLoader
{
    /**
     * @var \Composer\Autoload\ClassLoader
     */
    protected $composerLoader;

    /**
     * @var array
     */
    protected $proxies = [];

    /**
     * @var array
     */
    protected $injects = [];

    /**
     * @var array
     */
    protected $classAspects = [];

    /**
     * @var ProxyManager
     */
    protected $proxyManager;

    public function __construct(ComposerClassLoader $classLoader)
    {
        $this->composerLoader = $classLoader;
        $config = ScanConfig::instance();

        $scanner = new Scanner($this, $config);
        $classes = $scanner->scan($config->getPaths());
        $this->proxyManager = new ProxyManager($classes);
        $this->proxies = $this->proxyManager->getProxies();
        $this->injects = AnnotationCollector::getPropertiesByAnnotation(Inject::class);
        $this->classAspects = $this->getClassAspects();
        $this->initProxies();
    }

    public function initProxies()
    {
        $map = $this->composerLoader->getClassMap();
        $classes = [];
        foreach ($map as $class => $file) {
            $match = [];
            foreach ($this->classAspects as $aspect => $rules) {
                foreach ($rules as $rule) {
                    if (ProxyManager::isMatch($rule, $class)) {
                        $match[] = $aspect;
                    }
                }
            }
            if ($match) {
                $match = array_flip(array_flip($match));
                $classes[$class] = $match;
            }
        }

        $proxies = $this->proxyManager->generateProxyFiles($classes);
        $this->proxies = array_merge($this->proxies, $proxies);
    }

    public function loadClass(string $class): void
    {
        $path = $this->locateFile($class);

        if ($path !== false) {
            include $path;
        }
    }

    public static function init(): void
    {
        self::registerClassLoader();
    }

    public function getClassAspects(): array
    {
        $aspects = AspectCollector::get('classes', []);
        // Remove the useless aspect rules
        foreach ($aspects as $aspect => $rules) {
            if (!$rules) {
                unset($aspects[$aspect]);
            }
        }
        return $aspects;
    }

    public function getComposerLoader(): ComposerClassLoader
    {
        return $this->composerLoader;
    }

    protected function locateFile(string $className)
    {
        if (isset($this->proxies[$className]) && file_exists($this->proxies[$className])) {
            // echo '[Load Proxy] ' . $className . PHP_EOL;
            $file = $this->proxies[$className];
        } else {
            // if (!$this->proxyManager->isScaned($className)) {
            //     $match = [];
            //     foreach ($this->classAspects as $aspect => $rules) {
            //         foreach ($rules as $rule) {
            //             if (ProxyManager::isMatch($rule, $className)) {
            //                 $match[] = $aspect;
            //             }
            //         }
            //     }
            //     if ($match) {
            //         $match = array_flip(array_flip($match));
            //         $proxies = $this->proxyManager->generateProxyFiles([$className => $match]);
            //         $this->proxies = array_merge($this->proxies, $proxies);
            //         return $this->locateFile($className);
            //     }
            // }
            // echo '[Load Composer] ' . $className . PHP_EOL;
            $file = $this->composerLoader->findFile($className);
        }

        return $file;
    }

    protected static function registerClassLoader()
    {
        $loaders = spl_autoload_functions();

        // Proxy the composer loader
        foreach ($loaders as &$loader) {
            $unregisterLoader = $loader;
            if (is_array($loader) && $loader[0] instanceof ComposerClassLoader) {
                $composerLoader = $loader[0];
                AnnotationRegistry::registerLoader(function ($class) use ($composerLoader) {
                    $composerLoader->loadClass($class);
                    return class_exists($class, false);
                });
                $loader[0] = new static($composerLoader);
            }
            spl_autoload_unregister($unregisterLoader);
        }

        unset($loader);

        // Re-register the loaders
        foreach ($loaders as $loader) {
            spl_autoload_register($loader);
        }
    }
}
