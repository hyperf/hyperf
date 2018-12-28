<?php

namespace Hyperf\HttpServer\Annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;
use Hyperf\Di\ReflectionManager;
use Hyperf\HttpServer\Router\Router;
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

    public function collect(string $className, ?string $target): void
    {
        $prefix = $this->getPrefix();
        // @TODO The Router should init before annotation scan process.
        return;
        Router::addGroup($prefix, function () use ($className) {
            $class = ReflectionManager::reflectClass($className);
            $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
            }
        });
    }

    private function getPrefix()
    {
        return $this->prefix;
    }

}