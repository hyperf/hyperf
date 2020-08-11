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
namespace Hyperf\Utils;

use Composer\Autoload\ClassLoader;

class Composer
{
    /**
     * @var null|Collection
     */
    private static $content;

    /**
     * @var null|Collection
     */
    private static $json;

    /**
     * @var array
     */
    private static $extra = [];

    /**
     * @var array
     */
    private static $scripts = [];

    /**
     * @var array
     */
    private static $versions = [];

    /**
     * @var null|ClassLoader
     */
    private static $classLoader;

    /**
     * @throws \RuntimeException When composer.lock does not exist.
     */
    public static function getLockContent(): Collection
    {
        if (! self::$content) {
            $path = self::discoverLockFile();
            if (! $path) {
                throw new \RuntimeException('composer.lock not found.');
            }
            self::$content = collect(json_decode(file_get_contents($path), true));
            $packages = self::$content->offsetGet('packages') ?? [];
            $packagesDev = self::$content->offsetGet('packages-dev') ?? [];
            foreach (array_merge($packages, $packagesDev) as $package) {
                $packageName = '';
                foreach ($package ?? [] as $key => $value) {
                    if ($key === 'name') {
                        $packageName = $value;
                        continue;
                    }
                    switch ($key) {
                        case 'extra':
                            $packageName && self::$extra[$packageName] = $value;
                            break;
                        case 'scripts':
                            $packageName && self::$scripts[$packageName] = $value;
                            break;
                        case 'version':
                            $packageName && self::$versions[$packageName] = $value;
                            break;
                    }
                }
            }
        }
        return self::$content;
    }

    public static function getJsonContent(): Collection
    {
        if (! self::$json) {
            $path = BASE_PATH . '/composer.json';
            if (! is_readable($path)) {
                throw new \RuntimeException('composer.json is not readable.');
            }
            self::$json = collect(json_decode(file_get_contents($path), true));
        }
        return self::$json;
    }

    public static function discoverLockFile(): string
    {
        $path = '';
        if (is_readable(BASE_PATH . '/composer.lock')) {
            $path = BASE_PATH . '/composer.lock';
        }
        return $path;
    }

    public static function getMergedExtra(string $key = null)
    {
        if (! self::$extra) {
            self::getLockContent();
        }
        if ($key === null) {
            return self::$extra;
        }
        $extra = [];
        foreach (self::$extra ?? [] as $project => $config) {
            foreach ($config ?? [] as $configKey => $item) {
                if ($key === $configKey && $item) {
                    foreach ($item ?? [] as $k => $v) {
                        if (is_array($v)) {
                            $extra[$k] = array_merge($extra[$k] ?? [], $v);
                        } else {
                            $extra[$k][] = $v;
                        }
                    }
                }
            }
        }
        return $extra;
    }

    public static function getLoader(): ClassLoader
    {
        if (! self::$classLoader) {
            self::$classLoader = self::findLoader();
        }
        return self::$classLoader;
    }

    public static function setLoader(ClassLoader $classLoader): ClassLoader
    {
        self::$classLoader = $classLoader;
        return $classLoader;
    }

    private static function findLoader(): ClassLoader
    {
        $composerClass = '';
        foreach (get_declared_classes() as $declaredClass) {
            if (strpos($declaredClass, 'ComposerAutoloaderInit') === 0 && method_exists($declaredClass, 'getLoader')) {
                $composerClass = $declaredClass;
                break;
            }
        }
        if (! $composerClass) {
            throw new \RuntimeException('Composer loader not found.');
        }
        return $composerClass::getLoader();
    }
}
