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
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use function array_unique;
use function is_string;
use function sprintf;

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
        $this->middlewares = array_unique($middlewares);
        $this->coreHandler = $coreHandler;
        $this->container = $container;
    }

    protected function handleRequest($request)
    {
        if (! isset($this->middlewares[$this->offset]) && ! empty($this->coreHandler)) {
            $handler = $this->coreHandler;
        } else {
            $handler = $this->middlewares[$this->offset];
            is_string($handler) && $handler = $this->container->get($handler);
        }
        if (! method_exists($handler, 'process')) {
            throw new InvalidArgumentException(sprintf('Invalid middleware, it has to provide a process() method.'));
        }
        return $handler->process($request, $this->next());
    }

    protected function next(): self
    {
        ++$this->offset;
        return $this;
    }
}
