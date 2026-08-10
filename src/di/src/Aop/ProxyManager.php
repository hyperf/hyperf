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

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\AspectCollector;
use Hyperf\Support\Filesystem\Filesystem;
use RuntimeException;
use Throwable;

class ProxyManager
{
    public const LINE_MAP_VERSION = 3;

    /**
     * The classes which be rewritten by proxy.
     */
    protected array $proxies = [];

    /**
     * The line maps between the proxy files and the original class files.
     */
    protected array $lineMaps = [];

    protected bool $forceRegenerate = false;

    protected Filesystem $filesystem;

    /**
     * @param array $classMap the map to collect the classes with paths
     * @param string $proxyDir the directory which the proxy file places in
     * @param bool $lineMapEnabled whether to generate the line map file
     *                             and fix the exception location
     */
    public function __construct(
        protected array $classMap = [],
        protected string $proxyDir = '',
        protected bool $lineMapEnabled = true
    ) {
        if ($this->proxyDir !== '') {
            $this->proxyDir = rtrim($this->proxyDir, '/\\') . DIRECTORY_SEPARATOR;
        }
        $this->filesystem = new Filesystem();
        $this->proxies = $this->generateProxyFiles($this->initProxiesByReflectionClassMap(
            $this->classMap
        ));
    }

    public function getProxies(): array
    {
        return $this->proxies;
    }

    public function getProxyDir(): string
    {
        return $this->proxyDir;
    }

    public function getAspectClasses(): array
    {
        $aspectClasses = [];
        $classesAspects = AspectCollector::get('classes', []);
        foreach ($classesAspects as $aspect => $rules) {
            foreach ($rules as $rule) {
                if (isset($this->proxies[$rule])) {
                    $aspectClasses[$aspect][$rule] = $this->proxies[$rule];
                }
            }
        }
        return $aspectClasses;
    }

    public function getLineMapFilePath(): string
    {
        return $this->getProxyDir() . 'line-map.php';
    }

    public static function isLineMapCacheValid(string $proxyDir, bool $enabled): bool
    {
        $mapFile = rtrim($proxyDir, '/\\') . DIRECTORY_SEPARATOR . 'line-map.php';
        if (! $enabled) {
            if (is_file($mapFile)) {
                return false;
            }
            foreach (glob(rtrim($proxyDir, '/\\') . DIRECTORY_SEPARATOR . '*.proxy.php') ?: [] as $proxyFile) {
                if (str_contains((string) file_get_contents($proxyFile), '__hyperf_exception__')) {
                    return false;
                }
            }
            return true;
        }
        if (! is_file($mapFile)) {
            return false;
        }
        try {
            $data = (array) require $mapFile;
        } catch (Throwable) {
            return false;
        }
        return ($data['version'] ?? null) === self::LINE_MAP_VERSION
            && is_array($data['maps'] ?? null);
    }

    protected function generateProxyFiles(array $proxies = []): array
    {
        $proxyFiles = [];
        if (! $proxies && $this->getProxyDir() === '') {
            return $proxyFiles;
        }
        if (! file_exists($this->getProxyDir())) {
            mkdir($this->getProxyDir(), 0755, true);
        }
        if (! $proxies) {
            if ($this->lineMapEnabled) {
                $this->putLineMapFile();
            } elseif (is_file($this->getLineMapFilePath())) {
                $this->filesystem->delete($this->getLineMapFilePath());
            }
            return $proxyFiles;
        }
        if (! $this->lineMapEnabled) {
            // Remove the stale line map file when the feature is disabled.
            if (is_file($this->getLineMapFilePath())) {
                $this->filesystem->delete($this->getLineMapFilePath());
            }
            // WARNING: Ast class SHOULD NOT use static instance, because it will read  the code from file, then would be caused coroutine switch.
            $ast = new Ast();
            foreach ($proxies as $className => $aspects) {
                $proxyFiles[$className] = $this->putProxyFile($ast, $className);
            }
            return $proxyFiles;
        }
        // Reuse the existing line maps, so that the classes whose proxy
        // files are not modified do not need to be parsed again.
        if (is_file($this->getLineMapFilePath())) {
            try {
                $data = (array) require $this->getLineMapFilePath();
                if (($data['version'] ?? null) === self::LINE_MAP_VERSION && is_array($data['maps'] ?? null)) {
                    $this->lineMaps = array_intersect_key($data['maps'], $proxies);
                } else {
                    $this->forceRegenerate = true;
                }
            } catch (Throwable) {
                $this->forceRegenerate = true;
            }
        } else {
            $this->forceRegenerate = true;
        }
        // WARNING: Ast class SHOULD NOT use static instance, because it will read  the code from file, then would be caused coroutine switch.
        $ast = new Ast();
        foreach ($proxies as $className => $aspects) {
            $proxyFiles[$className] = $this->putProxyFile($ast, $className);
        }
        $this->putLineMapFile();
        return $proxyFiles;
    }

    protected function putProxyFile(Ast $ast, $className)
    {
        $proxyFilePath = $this->getProxyFilePath($className);
        $proxyFileExists = file_exists($proxyFilePath);
        $modified = ! $proxyFileExists
            || $this->forceRegenerate
            || ($this->lineMapEnabled && ! array_key_exists($className, $this->lineMaps));
        if ($proxyFileExists) {
            $modified = $modified
                || $this->isModified($className, $proxyFilePath)
                || (! $this->lineMapEnabled && str_contains((string) file_get_contents($proxyFilePath), '__hyperf_exception__'));
        }

        if ($modified) {
            $code = $ast->proxy($className);
            file_put_contents($proxyFilePath, $code);
            if ($this->lineMapEnabled) {
                // Build the line map from the final proxy code, so that the
                // exceptions thrown in the proxy file can be located in the
                // original class file.
                $this->lineMaps[$className] = $ast->buildLineMap($className, $code);
            }
        }

        return $proxyFilePath;
    }

    protected function putLineMapFile(): void
    {
        $data = [
            'version' => self::LINE_MAP_VERSION,
            'maps' => $this->lineMaps,
        ];
        $content = "<?php\n\n// Generated by Hyperf. Do not edit it!\n\nreturn " . $this->exportShortArray($data) . ";\n";
        // Write to the temporary file first, then rename it to avoid the
        // incomplete file when multiple workers are generating at the same time.
        $mapFile = $this->getLineMapFilePath();
        $tmpFile = tempnam($this->getProxyDir(), 'line-map.php.');
        if ($tmpFile === false) {
            throw new RuntimeException(sprintf('Unable to create a temporary line map file in %s.', $this->getProxyDir()));
        }
        try {
            if (file_put_contents($tmpFile, $content, LOCK_EX) === false || ! rename($tmpFile, $mapFile)) {
                throw new RuntimeException(sprintf('Unable to write the proxy line map file %s.', $mapFile));
            }
        } finally {
            if (is_file($tmpFile)) {
                $this->filesystem->delete($tmpFile);
            }
        }
    }

    /**
     * Export the array as the short array syntax, e.g. `[[1, 2], ['a' => 3]]`,
     * which is more compact and readable than the `var_export()` output.
     * The items of the associative arrays are separated by line breaks.
     */
    protected function exportShortArray(array $data, int $indent = 1): string
    {
        // Note: `range(0, -1)` returns `[0, -1]` instead of `[]`,
        // so the empty array must be checked first.
        $isList = $data === [] || array_keys($data) === range(0, count($data) - 1);
        $parts = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->exportShortArray($value, $indent + 1);
            } elseif (is_string($value)) {
                $value = var_export($value, true);
            } elseif (is_int($value) || is_float($value)) {
                $value = (string) $value;
            } elseif (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif ($value === null) {
                $value = 'null';
            }
            $parts[] = $isList ? $value : var_export($key, true) . ' => ' . $value;
        }
        if ($isList) {
            return '[' . implode(', ', $parts) . ']';
        }
        $padding = str_repeat('    ', $indent);
        return "[\n" . $padding . implode(",\n" . $padding, $parts) . ",\n" . str_repeat('    ', $indent - 1) . ']';
    }

    protected function isModified(string $className, ?string $proxyFilePath = null): bool
    {
        $proxyFilePath = $proxyFilePath ?? $this->getProxyFilePath($className);
        $time = $this->filesystem->lastModified($proxyFilePath);
        $origin = $this->classMap[$className];
        if ($time >= $this->filesystem->lastModified($origin)) {
            return false;
        }

        return true;
    }

    protected function getProxyFilePath($className)
    {
        return $this->getProxyDir() . str_replace('\\', '_', $className) . '.proxy.php';
    }

    protected function isMatch(string $rule, string $target): bool
    {
        if (str_contains($rule, '::')) {
            [$rule] = explode('::', $rule);
        }
        if (! str_contains($rule, '*') && $rule === $target) {
            return true;
        }
        $preg = str_replace(['*', '\\'], ['.*', '\\\\'], $rule);
        $pattern = "/^{$preg}$/";

        if (preg_match($pattern, $target)) {
            return true;
        }

        return false;
    }

    protected function initProxiesByReflectionClassMap(array $reflectionClassMap = []): array
    {
        // According to the data of AspectCollector to parse all the classes that need proxy.
        $proxies = [];
        if (! $reflectionClassMap) {
            return $proxies;
        }
        $classesAspects = AspectCollector::get('classes', []);
        foreach ($classesAspects as $aspect => $rules) {
            foreach ($rules as $rule) {
                foreach ($reflectionClassMap as $class => $path) {
                    if (! $this->isMatch($rule, $class)) {
                        continue;
                    }
                    $proxies[$class][] = $aspect;
                }
            }
        }

        foreach ($reflectionClassMap as $className => $path) {
            // Aggregate the class annotations
            $classAnnotations = $this->retrieveAnnotations($className . '._c');
            // Aggregate all methods annotations
            $methodAnnotations = $this->retrieveAnnotations($className . '._m');
            // Aggregate all properties annotations
            $propertyAnnotations = $this->retrieveAnnotations($className . '._p');
            $annotations = array_unique(array_merge($classAnnotations, $methodAnnotations, $propertyAnnotations));
            if ($annotations) {
                $annotationsAspects = AspectCollector::get('annotations', []);
                foreach ($annotationsAspects as $aspect => $rules) {
                    foreach ($rules as $rule) {
                        foreach ($annotations as $annotation) {
                            if ($this->isMatch($rule, $annotation)) {
                                $proxies[$className][] = $aspect;
                            }
                        }
                    }
                }
            }
        }
        return $proxies;
    }

    protected function retrieveAnnotations(string $annotationCollectorKey): array
    {
        $defined = [];
        $annotations = AnnotationCollector::get($annotationCollectorKey, []);

        foreach ($annotations as $name => $annotation) {
            if (is_object($annotation)) {
                $defined[] = $name;
            } else {
                $defined = array_merge($defined, array_keys($annotation));
            }
        }
        return $defined;
    }
}
