<?php


namespace Phar\core;


use Hyperf\Utils\Collection;

class Composer
{
    /**
     * @var array
     */
    private static $extra = [];

    /**
     * @var Collection
     */
    private static $content;

    /**
     * @var array
     */
    private static $scripts = [];

    /**
     * @var array
     */
    private static $versions = [];

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
            foreach (self::$content->offsetGet('packages') ?? [] as $package) {
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
}