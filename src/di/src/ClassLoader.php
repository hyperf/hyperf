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
namespace Hyperf\Di;

use Composer\Autoload\ClassLoader as ComposerClassLoader;
use Dotenv\Dotenv;
use Dotenv\Repository\Adapter;
use Dotenv\Repository\RepositoryBuilder;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\LazyLoader\LazyLoader;
use Hyperf\Di\ScanHandler\PcntlScanHandler;
use Hyperf\Di\ScanHandler\ScanHandlerInterface;
use Hyperf\Utils\Composer;

class ClassLoader
{
    protected ComposerClassLoader $composerClassLoader;

    public function __construct(ComposerClassLoader $classLoader, string $proxyFileDir, string $configDir, ScanHandlerInterface $handler)
    {
        $this->setComposerClassLoader($classLoader);
        if (file_exists(BASE_PATH . '/.env')) {
            $this->loadDotenv();
        }

        // Scan by ScanConfig to generate the reflection class map
        $config = ScanConfig::instance($configDir);
        $classLoader->addClassMap($config->getClassMap());

        $scanner = new Scanner($this, $config, $handler);
        $classLoader->addClassMap(
            $scanner->scan($classLoader->getClassMap(), $proxyFileDir)
        );
    }

    public function loadClass(string $class): void
    {
        $path = $this->locateFile($class);

        if ($path) {
            include $path;
        }
    }

    public static function init(?string $proxyFileDirPath = null, ?string $configDir = null, ?ScanHandlerInterface $handler = null): void
    {
        if (! $proxyFileDirPath) {
            // This dir is the default proxy file dir path of Hyperf
            $proxyFileDirPath = BASE_PATH . '/runtime/container/proxy/';
        }

        if (! $configDir) {
            // This dir is the default proxy file dir path of Hyperf
            $configDir = BASE_PATH . '/config/';
        }

        if (! $handler) {
            $handler = new PcntlScanHandler();
        }

        $loaders = spl_autoload_functions();

        // Proxy the composer class loader
        foreach ($loaders as &$loader) {
            $unregisterLoader = $loader;
            if (is_array($loader) && $loader[0] instanceof ComposerClassLoader) {
                /** @var ComposerClassLoader $composerClassLoader */
                $composerClassLoader = $loader[0];
                $loader[0] = new static($composerClassLoader, $proxyFileDirPath, $configDir, $handler);
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
        $file = $this->getComposerClassLoader()->findFile($className);
        return is_string($file) ? $file : null;
    }

    protected function loadDotenv(): void
    {
        $repository = RepositoryBuilder::createWithNoAdapters()
            ->addAdapter(Adapter\PutenvAdapter::class)
            ->immutable()
            ->make();

        Dotenv::create($repository, [BASE_PATH])->load();
    }
}
