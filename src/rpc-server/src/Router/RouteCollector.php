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

namespace Hyperf\RpcServer\Router;

use FastRoute\DataGenerator;
use FastRoute\RouteParser;
use Hyperf\HttpServer\Router\Handler;

class RouteCollector
{
    protected string $currentGroupPrefix;

    protected array $currentGroupOptions = [];

    /**
     * Constructs a route collector.
     */
    public function __construct(protected RouteParser $routeParser, protected DataGenerator $dataGenerator)
    {
        $this->currentGroupPrefix = '';
    }

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param mixed $handler
     */
    public function addRoute(string $route, $handler, array $options = [])
    {
        $route = $this->currentGroupPrefix . $route;
        $routeDatas = $this->routeParser->parse($route);
        foreach ($routeDatas as $routeData) {
            $this->dataGenerator->addRoute('POST', $routeData, new Handler($handler, $route));
        }
    }

    /**
     * Create a route group with a common prefix.
     *
     * All routes created in the passed callback will have the given group prefix prepended.
     *
     * @param string $prefix
     */
    public function addGroup($prefix, callable $callback, array $options = [])
    {
        $previousGroupPrefix = $this->currentGroupPrefix;
        $this->currentGroupPrefix = $previousGroupPrefix . $prefix;
        $callback($this);
        $this->currentGroupPrefix = $previousGroupPrefix;
    }

    /**
     * Returns the collected route data, as provided by the data generator.
     *
     * @return array
     */
    public function getData()
    {
        return $this->dataGenerator->getData();
    }
}
