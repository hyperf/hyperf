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
namespace Hyperf\Guzzle;

use GuzzleHttp\HandlerStack;
use Hyperf\Di\Container;
use Hyperf\Pool\SimplePool\PoolFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Coroutine;

class HandlerStackFactory
{
    /**
     * @var array
     */
    protected $option = [
        'min_connections' => 1,
        'max_connections' => 30,
        'wait_timeout' => 3.0,
        'max_idle_time' => 60,
    ];

    /**
     * @var array
     */
    protected $middlewares = [
        'retry' => [RetryMiddleware::class, [1, 10]],
    ];

    /**
     * @var bool
     */
    protected $usePoolHandler = false;

    public function __construct()
    {
        if (class_exists(ApplicationContext::class)) {
            $this->usePoolHandler = class_exists(PoolFactory::class) && ApplicationContext::getContainer() instanceof Container;
        }
    }

    public function create(array $option = [], array $middlewares = []): HandlerStack
    {
        $handler = null;
        $option = array_merge($this->option, $option);
        $middlewares = array_merge($this->middlewares, $middlewares);

        if (Coroutine::inCoroutine()) {
            $handler = $this->getHandler($option);
        }

        $stack = HandlerStack::create($handler);

        foreach ($middlewares as $key => $middleware) {
            if (is_array($middleware)) {
                [$class, $arguments] = $middleware;
                $middleware = new $class(...$arguments);
            }

            if ($middleware instanceof MiddlewareInterface) {
                $stack->push($middleware->getMiddleware(), $key);
            }
        }

        return $stack;
    }

    protected function getHandler(array $option)
    {
        if ($this->usePoolHandler) {
            return make(PoolHandler::class, [
                'option' => $option,
            ]);
        }

        if (class_exists(ApplicationContext::class)) {
            return make(CoroutineHandler::class);
        }

        return new CoroutineHandler();
    }
}
