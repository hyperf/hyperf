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
     * @inheritdoc
     */
    public function dispatch(...$params): ResponseInterface
    {
        /**
         * @var RequestInterface $request
         * @var array $middlewares
         * @var string $coreHandler
         */
        [$request, $middlewares, $coreHandler] = $params;
        $requestHandler = new HttpRequestHandler($middlewares, $coreHandler, $this->container);
        $response = $requestHandler->handle($request);
        return $response;
    }
}
