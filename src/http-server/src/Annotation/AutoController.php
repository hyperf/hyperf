<?php
declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpServer\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\ReflectionManager;
use Hyperf\HttpServer\Router\RouteMetadataCollector;
use Hyperf\Utils\Str;
use ReflectionMethod;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class AutoController extends AbstractAnnotation
{
    /**
     * @var string|null
     */
    public $prefix;

    public function __construct($value = null)
    {
        $this->value = $value;
        if (isset($value['prefix'])) {
            $this->prefix = $value['prefix'];
        }
    }

    public function collect(string $className, ?string $target): void
    {
        $prefix = $this->getPrefix($className);
        $class = ReflectionManager::reflectClass($className);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            $path = $this->parsePath($prefix, $method);
            RouteMetadataCollector::set($path, [
                'method' => 'GET',
                'handler' => [
                    $className,
                    $method->getName(),
                ],
            ]);
            if (Str::endsWith($path, '/index')) {
                RouteMetadataCollector::set(Str::replaceLast('/index', '', $path), [
                    'method' => 'GET',
                    'handler' => [
                        $className,
                        $method->getName(),
                    ],
                ]);
            }
        }
    }

    private function getPrefix(string $className): string
    {
        if (! $this->prefix) {
            $handledNamespace = Str::replaceFirst('Controller', '', Str::after($className, 'Controllers\\'));
            $handledNamespace = Str::replaceArray('\\', ['/'], $handledNamespace);
            $this->prefix = Str::lower($handledNamespace);
        }
        if ($this->prefix[0] !== '/') {
            $this->prefix = '/' . $this->prefix;
        }
        return $this->prefix;
    }

    private function parsePath(string $prefix, ReflectionMethod $method): string
    {
        return $prefix . '/' . $method->getName();
    }
}
