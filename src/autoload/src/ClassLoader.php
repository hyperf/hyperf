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
use Hyperf\Utils\Composer;

class ClassLoader
{
    /**
     * @var \Composer\Autoload\ClassLoader
     */
    protected $composerLoader;

    /**
     * The container to collect all the classes that would be proxy.
     * [ OriginalClassName => ProxyFileAbsolutePath ].
     *
     * @var array
     */
    protected $proxies = [];

    public function __construct(ComposerClassLoader $classLoader, string $proxyFileDir)
    {
        $this->composerLoader = $classLoader;
        // Scan by ScanConfig to generate the reflection class map
        $reflectionClassMap = (new Scanner($this, ScanConfig::instance()))->scan();
        // Get the class map of Composer loader
        $composerLoaderClassMap = $this->getComposerLoader()->getClassMap();
        $proxyManager = new ProxyManager($reflectionClassMap, $composerLoaderClassMap, $proxyFileDir);
        $this->proxies = $proxyManager->getProxies();
    }

    public function loadClass(string $class): void
    {
        $path = $this->locateFile($class);

        if ($path !== false) {
            include $path;
        }
    }

    public static function init(?string $proxyFileDirPath = null): void
    {
        if (! $proxyFileDirPath) {
            // This dir is the default proxy file dir path of Hyperf
            $proxyFileDirPath = BASE_PATH . '/runtime/container/proxy/';
        }

        $loaders = spl_autoload_functions();

        // Proxy the composer class loader
        foreach ($loaders as &$loader) {
            $unregisterLoader = $loader;
            if (is_array($loader) && $loader[0] instanceof ComposerClassLoader) {
                $composerLoader = $loader[0];
                AnnotationRegistry::registerLoader(function ($class) use ($composerLoader) {
                    $composerLoader->loadClass($class);
                    return class_exists($class, false);
                });
                $loader[0] = new static($composerLoader, $proxyFileDirPath);
            }
            spl_autoload_unregister($unregisterLoader);
        }

        unset($loader);

        // Re-register the loaders
        foreach ($loaders as $loader) {
            spl_autoload_register($loader);
        }
    }

    public function getComposerLoader(): ComposerClassLoader
    {
        return $this->composerLoader;
    }

    protected function locateFile(string $className)
    {
        if (isset($this->proxies[$className]) && file_exists($this->proxies[$className])) {
            $file = $this->proxies[$className];
        } else {
            $file = $this->getComposerLoader()->findFile($className);
        }

        return $file;
    }
}
