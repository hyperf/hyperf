<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Guzzle;

use GuzzleHttp\HandlerStack;
use Hyperf\Di\Container;
use Hyperf\Pool\SimplePool\PoolFactory;
use Hyperf\Utils\ApplicationContext;
use Swoole\Coroutine;

class HandlerStackFactory
{
    protected $defaultOption = [
        'min_connections' => 1,
        'max_connections' => 30,
        'wait_timeout' => 3.0,
        'max_idle_time' => 60,
        'middleware' => [
            'retry' => [RetryMiddleware::class, [1, 10]],
        ],
    ];

    protected $usePoolHandler = false;

    public function __construct()
    {
        if (class_exists(ApplicationContext::class)) {
            $this->usePoolHandler = class_exists(PoolFactory::class) && ApplicationContext::getContainer() instanceof Container;
        }
    }

    public function create(array $option = [])
    {
        $handler = null;
        $option = array_merge($this->defaultOption, $option);

        if (Coroutine::getCid() > 0) {
            if ($this->usePoolHandler) {
                $handler = make(PoolHandler::class, [
                    'option' => $option,
                ]);
            } else {
                $handler = new CoroutineHandler();
            }
        }

        $stack = HandlerStack::create($handler);

        foreach ($option['middleware'] ?? [] as $key => $middleware) {
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
}
