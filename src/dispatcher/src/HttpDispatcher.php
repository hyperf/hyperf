<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Dispatcher;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpDispatcher extends AbstractDispatcher
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(...$params): ResponseInterface
    {
        /**
         * @var RequestInterface
         * @var array $middlewares
         * @var string $coreHandler
         */
        [$request, $middlewares, $coreHandler] = $params;
        $requestHandler = new HttpRequestHandler($middlewares, $coreHandler, $this->container);
        return $requestHandler->handle($request);
    }
}
