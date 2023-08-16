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

use Dotenv\Dotenv;
use Dotenv\Repository\Adapter;
use Dotenv\Repository\RepositoryBuilder;
use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\LazyLoader\LazyLoader;
use Hyperf\Di\ScanHandler\PcntlScanHandler;
use Hyperf\Di\ScanHandler\ScanHandlerInterface;
use Hyperf\Support\Composer;

class ClassLoader
{
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

        $composerLoader = Composer::getLoader();

        if (file_exists(BASE_PATH . '/.env')) {
            static::loadDotenv();
        }

        // Scan by ScanConfig to generate the reflection class map
        $config = ScanConfig::instance($configDir);
        $composerLoader->addClassMap($config->getClassMap());

        $scanner = new Scanner($config, $handler);
        $composerLoader->addClassMap(
            $scanner->scan($composerLoader->getClassMap(), $proxyFileDirPath)
        );

        // Initialize Lazy Loader. This will prepend LazyLoader to the top of autoload queue.
        LazyLoader::bootstrap($configDir);
    }

    protected static function loadDotenv(): void
    {
        $repository = RepositoryBuilder::createWithNoAdapters()
            ->addAdapter(Adapter\PutenvAdapter::class)
            ->immutable()
            ->make();

        Dotenv::create($repository, [BASE_PATH])->load();
    }
}
