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

use FastRoute\DataGenerator;
use FastRoute\RouteParser;
use Hyperf\HttpServer\MiddlewareManager;

class RouteCollector
{
    protected string $currentGroupPrefix = '';

    protected array $currentGroupOptions = [];

    /**
     * Constructs a route collector.
     */
    public function __construct(protected RouteParser $routeParser, protected DataGenerator $dataGenerator, protected string $server = 'http')
    {
    }

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param array|string $handler
     */
    public function addRoute(array|string $httpMethod, string $route, mixed $handler, array $options = []): void
    {
        $route = $this->currentGroupPrefix . $route;
        $routeDataList = $this->routeParser->parse($route);
        $options = $this->mergeOptions($this->currentGroupOptions, $options);
        foreach ((array) $httpMethod as $method) {
            $method = strtoupper($method);
            foreach ($routeDataList as $routeData) {
                $this->dataGenerator->addRoute($method, $routeData, new Handler($handler, $route, $options));
            }

            MiddlewareManager::addMiddlewares($this->server, $route, $method, $options['middleware'] ?? []);
        }
    }

    /**
     * Create a route group with a common prefix.
     *
     * All routes created by the passed callback will have the given group prefix prepended.
     */
    public function addGroup(string $prefix, callable $callback, array $options = []): void
    {
        $previousGroupPrefix = $this->currentGroupPrefix;
        $currentGroupOptions = $this->currentGroupOptions;

        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $this->currentGroupOptions = $this->mergeOptions($currentGroupOptions, $options);
        $callback($this);

        $this->currentGroupPrefix = $previousGroupPrefix;
        $this->currentGroupOptions = $currentGroupOptions;
    }

    /**
     * Adds a GET route to the collection.
     *
     * This is simply an alias of $this->addRoute('GET', $route, $handler)
     * @param array|string $handler
     */
    public function get(string $route, mixed $handler, array $options = []): void
    {
        $this->addRoute('GET', $route, $handler, $options);
    }

    /**
     * Adds a POST route to the collection.
     *
     * This is simply an alias of $this->addRoute('POST', $route, $handler)
     * @param array|string $handler
     */
    public function post(string $route, mixed $handler, array $options = []): void
    {
        $this->addRoute('POST', $route, $handler, $options);
    }

    /**
     * Adds a PUT route to the collection.
     *
     * This is simply an alias of $this->addRoute('PUT', $route, $handler)
     * @param array|string $handler
     */
    public function put(string $route, mixed $handler, array $options = []): void
    {
        $this->addRoute('PUT', $route, $handler, $options);
    }

    /**
     * Adds a DELETE route to the collection.
     *
     * This is simply an alias of $this->addRoute('DELETE', $route, $handler)
     * @param array|string $handler
     */
    public function delete(string $route, mixed $handler, array $options = []): void
    {
        $this->addRoute('DELETE', $route, $handler, $options);
    }

    /**
     * Adds a PATCH route to the collection.
     *
     * This is simply an alias of $this->addRoute('PATCH', $route, $handler)
     * @param array|string $handler
     */
    public function patch(string $route, mixed $handler, array $options = []): void
    {
        $this->addRoute('PATCH', $route, $handler, $options);
    }

    /**
     * Adds a HEAD route to the collection.
     *
     * This is simply an alias of $this->addRoute('HEAD', $route, $handler)
     * @param array|string $handler
     */
    public function head(string $route, mixed $handler, array $options = []): void
    {
        $this->addRoute('HEAD', $route, $handler, $options);
    }

    /**
     * Returns the collected route data, as provided by the data generator.
     */
    public function getData(): array
    {
        return $this->dataGenerator->getData();
    }

    public function getRouteParser(): RouteParser
    {
        return $this->routeParser;
    }

    protected function mergeOptions(array $origin, array $options): array
    {
        return array_merge_recursive($origin, $options);
    }
}
