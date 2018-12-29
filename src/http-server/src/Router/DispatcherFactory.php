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

use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteCollector;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Exception\ConflictAnnotationException;
use Hyperf\Di\ReflectionManager;
use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use ReflectionMethod;

class DispatcherFactory
{
    protected $routes = [BASE_PATH . '/config/routes.php'];

    protected $routeCollector = RouteCollector::class;

    /**
     * @var \FastRoute\RouteCollector
     */
    private $router;

    public function __invoke(ContainerInterface $container): Dispatcher
    {
        /** @var RouteCollector $router */
        // @TODO Use a Interface instead of the specified class.
        $this->router = $router = $container->get($this->routeCollector);

        foreach ($this->routes as $route) {
            require_once $route;
        }

        $this->initAnnotationRoute(AnnotationCollector::getContainer());

        return new GroupCountBased($this->router->getData());
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
        foreach ($methods as $method) {
            $path = $this->parsePath($prefix, $method);
            $this->router->addRoute(['GET'], $path, [$className, $method->getName()]);
            if (Str::endsWith($path, '/index')) {
                $path = Str::replaceLast('/index', '', $path);
                $this->router->addRoute(['GET'], $path, [$className, $method->getName()]);
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
        $this->router->addGroup($prefix, function () use ($className, $methodMetadata) {
            foreach ($methodMetadata as $method => $values) {
                if (isset($values[RequestMapping::class])) {
                    $item = $values[RequestMapping::class];
                    if ($item['path'][0] !== '/') {
                        $item['path'] = '/' . $item['path'];
                    }
                    $this->router->addRoute($item['methods'], $item['path'], [
                        $className,
                        $method
                    ]);
                }
            }
        });
    }

    private function getPrefix(string $className, string $prefix): string
    {
        if (! $prefix) {
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
