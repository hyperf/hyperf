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
namespace Hyperf\Di;

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\Aop\ProxyManager;
use Hyperf\Di\LazyLoader\LazyLoader;
use Hyperf\Utils\Composer;

class ClassLoader
{
    /**
     * @var \Composer\Autoload\ClassLoader
     */
    protected $composerClassLoader;

    /**
     * The container to collect all the classes that would be proxy.
     * [ OriginalClassName => ProxyFileAbsolutePath ].
     *
     * @var array
     */
    protected $proxies = [];

    public function __construct(ComposerClassLoader $classLoader, string $proxyFileDir, string $configDir)
    {
        $this->setComposerClassLoader($classLoader);
        $start = microtime(true);
        // Scan by ScanConfig to generate the reflection class map
        $scanner = new Scanner($this, $config = ScanConfig::instance());
        $classLoader->addClassMap($config->getClassMap());
        timepoint();
        $reflectionClassMap = $scanner->scan();
        timepoint('Scan');
        // Get the class map of Composer loader
        $composerLoaderClassMap = $this->getComposerClassLoader()->getClassMap();
        $proxyManager = new ProxyManager($reflectionClassMap, $composerLoaderClassMap, $proxyFileDir, $configDir);
        timepoint('InitProxyManager');
        $this->proxies = $proxyManager->getProxies();
    }

    public function loadClass(string $class): void
    {
        $path = $this->locateFile($class);

        if ($path) {
            include $path;
        }
    }

    public static function init(?string $proxyFileDirPath = null, ?string $configDir = null): void
    {
        if (! $proxyFileDirPath) {
            // This dir is the default proxy file dir path of Hyperf
            $proxyFileDirPath = BASE_PATH . '/runtime/container/proxy/';
        }

        if (! $configDir) {
            // This dir is the default proxy file dir path of Hyperf
            $configDir = BASE_PATH . '/config/';
        }

        $loaders = spl_autoload_functions();

        // Proxy the composer class loader
        foreach ($loaders as &$loader) {
            $unregisterLoader = $loader;
            if (is_array($loader) && $loader[0] instanceof ComposerClassLoader) {
                /** @var ComposerClassLoader $composerClassLoader */
                $composerClassLoader = $loader[0];
                AnnotationRegistry::registerLoader(function ($class) use ($composerClassLoader) {
                    return (bool) $composerClassLoader->findFile($class);
                });
                $loader[0] = new static($composerClassLoader, $proxyFileDirPath, $configDir);
            }
            spl_autoload_unregister($unregisterLoader);
        }

        unset($loader);

        // Re-register the loaders
        foreach ($loaders as $loader) {
            spl_autoload_register($loader);
        }

        // Initialize Lazy Loader. This will prepend LazyLoader to the top of autoload queue.
        LazyLoader::bootstrap($configDir);
    }

    public function setComposerClassLoader(ComposerClassLoader $classLoader): self
    {
        $this->composerClassLoader = $classLoader;
        // Set the ClassLoader to Hyperf\Utils\Composer to avoid unnecessary find process.
        Composer::setLoader($classLoader);
        return $this;
    }

    public function getComposerClassLoader(): ComposerClassLoader
    {
        return $this->composerClassLoader;
    }

    protected function locateFile(string $className): ?string
    {
        if (isset($this->proxies[$className]) && file_exists($this->proxies[$className])) {
            $file = $this->proxies[$className];
        } else {
            $file = $this->getComposerClassLoader()->findFile($className);
        }

        return is_string($file) ? $file : null;
    }
}
