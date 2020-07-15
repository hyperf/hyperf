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
namespace Hyperf\HttpServer\Router;

use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteParser\Std;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Exception\ConflictAnnotationException;
use Hyperf\Di\ReflectionManager;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Mapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\PatchMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Utils\Str;
use ReflectionMethod;

class DispatcherFactory
{
    protected $routes = [BASE_PATH . '/config/routes.php'];

    /**
     * @var RouteCollector[]
     */
    protected $routers = [];

    /**
     * @var Dispatcher[]
     */
    protected $dispatchers = [];

    public function __construct()
    {
        $this->initAnnotationRoute(AnnotationCollector::list());
        $this->initConfigRoute();
    }

    public function getDispatcher(string $serverName): Dispatcher
    {
        if (isset($this->dispatchers[$serverName])) {
            return $this->dispatchers[$serverName];
        }

        $router = $this->getRouter($serverName);
        return $this->dispatchers[$serverName] = new GroupCountBased($router->getData());
    }

    public function initConfigRoute()
    {
        Router::init($this);
        foreach ($this->routes as $route) {
            if (file_exists($route)) {
                require_once $route;
            }
        }
    }

    public function getRouter(string $serverName): RouteCollector
    {
        if (isset($this->routers[$serverName])) {
            return $this->routers[$serverName];
        }

        $parser = new Std();
        $generator = new DataGenerator();
        return $this->routers[$serverName] = new RouteCollector($parser, $generator, $serverName);
    }

    protected function initAnnotationRoute(array $collector): void
    {
        foreach ($collector as $className => $metadata) {
            if (isset($metadata['_c'][AutoController::class])) {
                if ($this->hasControllerAnnotation($metadata['_c'])) {
                    $message = sprintf('AutoController annotation can\'t use with Controller annotation at the same time in %s.', $className);
                    throw new ConflictAnnotationException($message);
                }
                $middlewares = $this->handleMiddleware($metadata['_c']);
                $this->handleAutoController($className, $metadata['_c'][AutoController::class], $middlewares, $metadata['_m'] ?? []);
            }
            if (isset($metadata['_c'][Controller::class])) {
                $middlewares = $this->handleMiddleware($metadata['_c']);
                $this->handleController($className, $metadata['_c'][Controller::class], $metadata['_m'] ?? [], $middlewares);
            }
        }
    }

    /**
     * Register route according to AutoController annotation.
     */
    protected function handleAutoController(string $className, AutoController $annotation, array $middlewares = [], array $methodMetadata = []): void
    {
        $class = ReflectionManager::reflectClass($className);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $prefix = $this->getPrefix($className, $annotation->prefix);
        $router = $this->getRouter($annotation->server);

        $autoMethods = ['GET', 'POST', 'HEAD'];
        $defaultAction = '/index';
        foreach ($methods as $method) {
            $path = $this->parsePath($prefix, $method);
            $methodName = $method->getName();
            if (substr($methodName, 0, 2) === '__') {
                continue;
            }

            $methodMiddlewares = $middlewares;
            // Handle method level middlewares.
            if (isset($methodMetadata[$methodName])) {
                $methodMiddlewares = array_merge($methodMiddlewares, $this->handleMiddleware($methodMetadata[$methodName]));
                $methodMiddlewares = array_unique($methodMiddlewares);
            }

            $router->addRoute($autoMethods, $path, [$className, $methodName], [
                'middleware' => $methodMiddlewares,
            ]);

            if (Str::endsWith($path, $defaultAction)) {
                $path = Str::replaceLast($defaultAction, '', $path);
                $router->addRoute($autoMethods, $path, [$className, $methodName], [
                    'middleware' => $methodMiddlewares,
                ]);
            }
        }
    }

    /**
     * Register route according to Controller and XxxMapping annotations.
     * Including RequestMapping, GetMapping, PostMapping, PutMapping, PatchMapping, DeleteMapping.
     */
    protected function handleController(string $className, Controller $annotation, array $methodMetadata, array $middlewares = []): void
    {
        if (! $methodMetadata) {
            return;
        }
        $prefix = $this->getPrefix($className, $annotation->prefix);
        $router = $this->getRouter($annotation->server);
        $mappingAnnotations = [
            RequestMapping::class,
            GetMapping::class,
            PostMapping::class,
            PutMapping::class,
            PatchMapping::class,
            DeleteMapping::class,
        ];

        foreach ($methodMetadata as $methodName => $values) {
            $methodMiddlewares = $middlewares;
            // Handle method level middlewares.
            if (isset($values)) {
                $methodMiddlewares = array_merge($methodMiddlewares, $this->handleMiddleware($values));
                $methodMiddlewares = array_unique($methodMiddlewares);
            }

            foreach ($mappingAnnotations as $mappingAnnotation) {
                /** @var Mapping $mapping */
                if ($mapping = $values[$mappingAnnotation] ?? null) {
                    if (! isset($mapping->path) || ! isset($mapping->methods)) {
                        continue;
                    }
                    $path = $mapping->path;

                    if ($path === '') {
                        $path = $prefix;
                    } elseif ($path[0] !== '/') {
                        $path = $prefix . '/' . $path;
                    }
                    $router->addRoute($mapping->methods, $path, [$className, $methodName], [
                        'middleware' => $methodMiddlewares,
                    ]);
                }
            }
        }
    }

    protected function getPrefix(string $className, string $prefix): string
    {
        if (! $prefix) {
            $handledNamespace = Str::replaceFirst('Controller', '', Str::after($className, '\\Controller\\'));
            $handledNamespace = str_replace('\\', '/', $handledNamespace);
            $prefix = Str::snake($handledNamespace);
            $prefix = str_replace('/_', '/', $prefix);
        }
        if ($prefix[0] !== '/') {
            $prefix = '/' . $prefix;
        }
        return $prefix;
    }

    protected function parsePath(string $prefix, ReflectionMethod $method): string
    {
        return $prefix . '/' . $method->getName();
    }

    protected function hasControllerAnnotation(array $item): bool
    {
        return isset($item[Controller::class]);
    }

    protected function handleMiddleware(array $metadata): array
    {
        $hasMiddlewares = isset($metadata[Middlewares::class]);
        $hasMiddleware = isset($metadata[Middleware::class]);
        if (! $hasMiddlewares && ! $hasMiddleware) {
            return [];
        }
        if ($hasMiddlewares && $hasMiddleware) {
            throw new ConflictAnnotationException('Could not use @Middlewares and @Middleware annotation at the same times at same level.');
        }
        if ($hasMiddlewares) {
            // @Middlewares
            /** @var Middlewares $middlewares */
            $middlewares = $metadata[Middlewares::class];
            $result = [];
            foreach ($middlewares->middlewares as $middleware) {
                $result[] = $middleware->middleware;
            }
            return $result;
        }
        // @Middleware
        /** @var Middleware $middleware */
        $middleware = $metadata[Middleware::class];
        return [$middleware->middleware];
    }
}
