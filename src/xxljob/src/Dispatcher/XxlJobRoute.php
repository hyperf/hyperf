<?php

declare(strict_types=1);

namespace Hyperf\XxlJob\Dispatcher;

use Hyperf\HttpServer\Router\RouteCollector;
use Hyperf\XxlJob\Middleware\JobMiddleware;

class XxlJobRoute
{
    public function add(RouteCollector $route, $prefixUrl)
    {
        /*
         * 心跳
         */
        $route->addRoute(['POST'], '/' . $prefixUrl . '/beat', [JobController::class, 'beat'], ['middleware' => [JobMiddleware::class]]);

        /*
         * 触发任务执行
         */
        $route->addRoute(['POST'], '/' . $prefixUrl . '/run', [JobController::class, 'run'], ['middleware' => [JobMiddleware::class]]);

        /*
         * 忙碌检测
         */
        $route->addRoute(['POST'], '/' . $prefixUrl . '/idleBeat', [JobController::class, 'idleBeat'], ['middleware' => [JobMiddleware::class]]);

        /*
         * 终止任务
         */
        $route->addRoute(['POST'], '/' . $prefixUrl . '/kill', [JobController::class, 'kill'], ['middleware' => [JobMiddleware::class]]);

        /*
         * 日志
         */
        $route->addRoute(['POST'], '/' . $prefixUrl . '/log', [JobController::class, 'log'], ['middleware' => [JobMiddleware::class]]);
    }
}
