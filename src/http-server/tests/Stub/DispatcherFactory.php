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

namespace HyperfTest\HttpServer\Stub;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Router\RouteCollector;

class DispatcherFactory extends \Hyperf\HttpServer\Router\DispatcherFactory
{
    public function getPrefix(string $className, string $prefix): string
    {
        return parent::getPrefix($className, $prefix);
    }

    public function handleAutoController(string $className, AutoController $annotation, array $middlewares = [], array $methodMetadata = []): void
    {
        parent::handleAutoController($className, $annotation, $middlewares, $methodMetadata);
    }

    public function handleController(string $className, Controller $annotation, array $methodMetadata, array $middlewares = []): void
    {
        parent::handleController($className, $annotation, $methodMetadata, $middlewares);
    }

    public function getRouter(string $serverName): RouteCollector
    {
        return parent::getRouter($serverName);
    }
}
