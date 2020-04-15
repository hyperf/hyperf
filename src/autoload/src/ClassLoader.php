<?php

namespace Hyperf\Autoload;


use App\Foo;
use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Hyperf\Config\ProviderConfig;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AspectCollector;

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
        $configs = ProviderConfig::load();

        $scanner = new Scanner($this);
        $paths = array_merge([
            // @TODO 优化 app 路径为可配置的
            BASE_PATH . '/app',
        ], $configs['annotations']['scan']['paths'] ?? []);
        $classes = $scanner->scan($paths);
        $this->proxies = ProxyManager::init($classes);
        var_dump($this->proxies);
    }

    public function loadClass(string $class): void
    {
        $path = $this->locateFile($class);

        if ($path !== false) {
            include $path;
        }
    }

    protected function locateFile(string $className)
    {
        if (isset($this->proxies[$className]) && file_exists($this->proxies[$className])) {
            echo '[Load Proxy] ' . $className . PHP_EOL;
            $file = $this->proxies[$className];
        } else {
            echo '[Load Composer] ' . $className . PHP_EOL;
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

    public static function init(): void
    {
        self::registerClassLoader();
    }

    public function getComposerLoader(): ComposerClassLoader
    {
        return $this->composerLoader;
    }

}
