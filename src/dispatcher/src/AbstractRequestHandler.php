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
namespace Hyperf\Dispatcher;

use Hyperf\Dispatcher\Exceptions\InvalidArgumentException;
use Hyperf\HttpServer\Annotation\Middleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class AbstractRequestHandler
 * @package Hyperf\Dispatcher
 * @mixin RequestHandlerInterface
 */
abstract class AbstractRequestHandler
{
    /**
     * @var array
     */
    protected $middlewares = [];

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var MiddlewareInterface|object
     */
    protected $coreHandler;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param array $middlewares All middlewares to dispatch by dispatcher
     * @param MiddlewareInterface|object $coreHandler The core middleware of dispatcher
     */
    public function __construct(array $middlewares, $coreHandler, ContainerInterface $container)
    {
        $this->middlewares = $middlewares;
        $this->coreHandler = $coreHandler;
        $this->container = $container;
    }

    protected function handleRequest($request)
    {
        if (! isset($this->middlewares[$this->offset]) && ! empty($this->coreHandler)) {
            $handler = $this->coreHandler;
        } else {
            /** @var Middleware $middleware */
            $middleware = $this->middlewares[$this->offset];
            $handler = $this->container->get($middleware->middleware);
        }
        if (! method_exists($handler, 'process')) {
            throw new InvalidArgumentException(sprintf('Invalid middleware, it has to provide a process() method.'));
        }
        return call_user_func([$handler,'process'],$request,$this->next(),...$middleware->arguments ?? []);
    }

    /**
     * @return static | RequestHandlerInterface
     */
    protected function next() :self
    {
        ++$this->offset;
        return $this;
    }
}
