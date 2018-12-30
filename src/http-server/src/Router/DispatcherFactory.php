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

namespace Hyperf\HttpServer\Router;

use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Exception\ConflictAnnotationException;
use Hyperf\Di\ReflectionManager;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PatchMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use ReflectionMethod;

class DispatcherFactory
{
    protected $routes = [BASE_PATH . '/config/routes.php'];

    /**
     * @var \FastRoute\RouteCollector[]
     */
    private $routers = [];

    /**
     * @var Dispatcher[]
     */
    private $dispatchers = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->initAnnotationRoute(AnnotationCollector::getContainer());
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
            require_once $route;
        }
    }

    public function getRouter(string $serverName): RouteCollector
    {
        if (isset($this->routers[$serverName])) {
            return $this->routers[$serverName];
        }

        $parser = new Std();
        $generator = new DataGenerator();
        return $this->routers[$serverName] = new RouteCollector($parser, $generator);
    }

    private function initAnnotationRoute(array $collector): void
    {
        foreach ($collector as $className => $metadata) {
            if (isset($metadata['_c'][AutoController::class])) {
                if ($this->hasControllerAnnotation($metadata['_c'])) {
                    $message = sprintf('AutoController annotation can\'t use with Controller annotation at the same time in %s.', $className);
                    throw new ConflictAnnotationException($message);
                }
                $this->handleAutoController($className, $metadata['_c'][AutoController::class]);
            }
            if (isset($metadata['_c'][Controller::class])) {
                $this->handleController($className, $metadata['_c'][Controller::class], $metadata['_m']);
            }
        }
    }

    /**
     * Register route according to AutoController annotation.
     */
    private function handleAutoController(string $className, array $values): void
    {
        $class = ReflectionManager::reflectClass($className);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        $prefix = $this->getPrefix($className, $values['prefix'] ?? '');
        $router = $this->getRouter($values['server'] ?? 'httpServer');

        foreach ($methods as $method) {
            $path = $this->parsePath($prefix, $method);
            $router->addRoute(['GET'], $path, [$className, $method->getName()]);
            if (Str::endsWith($path, '/index')) {
                $path = Str::replaceLast('/index', '', $path);
                $router->addRoute(['GET'], $path, [$className, $method->getName()]);
            }
        }
    }

    /**
     * Register route according to Controller and XxxMapping annotations.
     * Including RequestMapping, GetMapping, PostMapping, PutMapping, PatchMapping, DeleteMapping.
     */
    private function handleController(string $className, array $controllerMetadata, array $methodMetadata): void
    {
        $prefix = $this->getPrefix($className, $controllerMetadata['prefix'] ?? '');
        $router = $this->getRouter($controllerMetadata['prefix'] ?? 'httpServer');

        $router->addGroup($prefix, function ($router) use ($className, $methodMetadata) {
            foreach ($methodMetadata as $method => $values) {
                $mappingAnnotations = [
                    RequestMapping::class,
                    GetMapping::class,
                    PostMapping::class,
                    PutMapping::class,
                    PatchMapping::class,
                    DeleteMapping::class,
                ];
                foreach ($mappingAnnotations as $mappingAnnotation) {
                    if (isset($values[$mappingAnnotation])) {
                        $item = $values[$mappingAnnotation];
                        if ($item['path'][0] !== '/') {
                            $item['path'] = '/' . $item['path'];
                        }
                        $router->addRoute($item['methods'], $item['path'], [
                            $className,
                            $method
                        ]);
                    }
                }
            }
        });
    }

    private function getPrefix(string $className, string $prefix): string
    {
        if (!$prefix) {
            $handledNamespace = Str::replaceFirst('Controller', '', Str::after($className, '\\Controllers\\'));
            $handledNamespace = Str::replaceArray('\\', ['/'], $handledNamespace);
            $prefix = Str::snake($handledNamespace);
        }
        if ($prefix[0] !== '/') {
            $prefix = '/' . $prefix;
        }
        return $prefix;
    }

    private function parsePath(string $prefix, ReflectionMethod $method): string
    {
        return $prefix . '/' . $method->getName();
    }

    private function hasControllerAnnotation(array $item): bool
    {
        return isset($item[Controller::class]);
    }
}
