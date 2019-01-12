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

namespace Hyperf\Dispatcher;

use Hyperf\Dispatcher\Exceptions\InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function array_unique;
use function is_string;

class HttpRequestHandler implements RequestHandlerInterface
{
    private $middlewares = [];

    private $offset = 0;

    /**
     * @var string
     */
    private $coreHandler;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(array $middlewares, MiddlewareInterface $coreHandler, ContainerInterface $container)
    {
        $this->middlewares = array_unique($middlewares);
        $this->coreHandler = $coreHandler;
        $this->container = $container;
    }

    /**
     * Handles a request and produces a response.
     * May call other collaborating code to generate the response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (! isset($this->middlewares[$this->offset]) && ! empty($this->coreHandler)) {
            $handler = $this->coreHandler;
        } else {
            $handler = $this->middlewares[$this->offset];
            is_string($handler) && $handler = $this->container->get($handler);
        }
        if (! method_exists($handler, 'process')) {
            throw new InvalidArgumentException(sprintf('Invalid middleware, it have to provide a process() method.'));
        }
        return $handler->process($request, $this->next());
    }

    private function next()
    {
        $this->offset++;
        return $this;
    }
}
