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

namespace Hyperf\Di\Aop;

use Throwable;

/**
 * Resolve proxy locations to their original source locations without
 * modifying the Throwable object or its engine-managed trace.
 */
class LineMapFixer
{
    /**
     * The loaded line maps, lazy loaded from the `line-map.php` file.
     */
    private static array $mapsByDirectory = [];

    /**
     * Format an exception with proxy locations translated to source locations.
     */
    public static function format(Throwable $exception): string
    {
        return self::formatText($exception, (string) $exception);
    }

    /**
     * Translate proxy locations in already-rendered exception output.
     */
    public static function formatText(Throwable $exception, string $text): string
    {
        $replacements = [];
        for ($current = $exception; $current !== null; $current = $current->getPrevious()) {
            self::addReplacement(
                $current->getFile(),
                $current->getLine(),
                $replacements
            );
            foreach ($current->getTrace() as $frame) {
                if (isset($frame['file'], $frame['line'])) {
                    self::addReplacement($frame['file'], $frame['line'], $replacements);
                }
            }
        }

        return strtr($text, $replacements);
    }

    /**
     * Resolve one proxy file location. Unmapped locations are returned unchanged.
     *
     * @return array{file: string, line: int}
     */
    public static function resolveLocation(string $file, int $line): array
    {
        if (! self::isProxyFile($file)) {
            return ['file' => $file, 'line' => $line];
        }

        $map = self::getMapByFile($file);
        $originLine = $map === null ? null : self::translate($map, $line);
        if ($originLine === null || ! is_string($map['file'] ?? null)) {
            return ['file' => $file, 'line' => $line];
        }

        return ['file' => $map['file'], 'line' => $originLine];
    }

    private static function addReplacement(
        string $file,
        int $line,
        array &$replacements
    ): void {
        $location = self::resolveLocation($file, $line);
        if ($location['file'] === $file && $location['line'] === $line) {
            return;
        }

        foreach ([
            [sprintf('%s:%d', $file, $line), sprintf('%s:%d', $location['file'], $location['line'])],
            [sprintf('%s(%d)', $file, $line), sprintf('%s(%d)', $location['file'], $location['line'])],
            [sprintf('%s on line %d', $file, $line), sprintf('%s on line %d', $location['file'], $location['line'])],
        ] as [$from, $to]) {
            $replacements[$from] = $to;
        }
    }

    /**
     * Find the map by the proxy filename. The map file is stored next to
     * generated proxies and keys maps by the original class name.
     */
    private static function getMapByFile(string $proxyFile): ?array
    {
        $proxyName = basename($proxyFile);
        foreach (self::loadMaps(dirname($proxyFile)) as $className => $map) {
            if ($proxyName === str_replace('\\', '_', $className) . '.proxy.php') {
                return is_array($map) ? $map : null;
            }
        }
        return null;
    }

    /**
     * Load the line maps from the `line-map.php` file which is next
     * to the proxy files. The maps are only loaded once.
     */
    private static function loadMaps(string $proxyDir): array
    {
        $proxyDir = realpath($proxyDir) ?: $proxyDir;
        if (array_key_exists($proxyDir, self::$mapsByDirectory)) {
            return self::$mapsByDirectory[$proxyDir];
        }
        $mapFile = $proxyDir . '/line-map.php';
        if (! is_file($mapFile)) {
            return self::$mapsByDirectory[$proxyDir] = [];
        }
        try {
            $data = (array) require $mapFile;
            $maps = is_array($data['maps'] ?? null) ? $data['maps'] : $data;
        } catch (Throwable) {
            $maps = [];
        }
        return self::$mapsByDirectory[$proxyDir] = $maps;
    }

    /**
     * Translate the line number in the proxy file to the line number
     * in the original file by the narrowest matched range.
     */
    private static function translate(array $map, int $line): ?int
    {
        $best = null;
        foreach ($map['ranges'] ?? [] as $range) {
            [$proxyStart, $proxyEnd] = $range;
            if ($line < $proxyStart || $line > $proxyEnd) {
                continue;
            }
            if ($best === null || ($proxyEnd - $proxyStart) < ($best[1] - $best[0])) {
                $best = $range;
            }
        }
        if ($best === null) {
            return null;
        }
        return min($best[3], $best[2] + ($line - $best[0]));
    }

    private static function isProxyFile(string $file): bool
    {
        return str_ends_with($file, '.proxy.php');
    }
}
