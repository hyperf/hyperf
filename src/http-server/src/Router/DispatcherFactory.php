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
use Hyperf\Utils\Str;
use Psr\Container\ContainerInterface;
use ReflectionMethod;

class DispatcherFactory
{
    protected $routes = [BASE_PATH . '/config/routes.php'];

    protected $routeCollector = RouteCollector::class;

    public function __invoke(ContainerInterface $container): Dispatcher
    {
        /** @var RouteCollector $router */
        // @TODO Use a Interface instead of the specified class.
        $router = $container->get($this->routeCollector);

        foreach ($this->routes as $route) {
            require_once $route;
        }

        $this->initAnnotationRoute($router, AnnotationCollector::getContainer());

        return new GroupCountBased($router->getData());
    }

    private function initAnnotationRoute(RouteCollector $router, array $collector)
    {
        foreach ($collector as $className => $metadata) {
            if (! isset($metadata['_c'][AutoController::class])) {
                continue;
            }
            if ($this->hasControllerAnnotation($metadata['_c'])) {
                throw new ConflictAnnotationException('AutoController annotation can\'t use with Controller annotation at the same time.');
            }
            $values = $metadata['_c'][AutoController::class];
            $class = ReflectionManager::reflectClass($className);
            $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
            $prefix = $this->getPrefix($className, $values['prefix'] ?? '');
            foreach ($methods as $method) {
                $path = $this->parsePath($prefix, $method);
                $router->addRoute(['GET'], $path, [$className, $method->getName()]);
                if (Str::endsWith($path, '/index')) {
                    $path = Str::replaceLast('/index', '', $path);
                    $router->addRoute(['GET'], $path, [$className, $method->getName()]);
                }
            }
        }
    }

    private function getPrefix(string $className, string $prefix): string
    {
        if (! $prefix) {
            $handledNamespace = Str::replaceFirst('Controller', '', Str::after($className, 'Controllers\\'));
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
