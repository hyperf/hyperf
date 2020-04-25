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
use Hyperf\Di\Annotation\AspectCollector;
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

    public function __construct(ComposerClassLoader $classLoader)
    {
        $this->composerLoader = $classLoader;
        $config = ScanConfig::instance();

        $scanner = new Scanner($this, $config);
        $reflectionClassMap = $scanner->scan($config->getPaths(), $config->getCacheNamespaces(), $config->getCollectors());
        $proxyManager = new ProxyManager($reflectionClassMap, $this->getComposerLoader()->getClassMap());
        $this->proxies = $proxyManager->getProxies();
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

    public function getComposerLoader(): ComposerClassLoader
    {
        return $this->composerLoader;
    }

    protected function locateFile(string $className)
    {
        if (isset($this->proxies[$className]) && file_exists($this->proxies[$className])) {
            $file = $this->proxies[$className];
        } else {
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
