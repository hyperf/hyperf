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

namespace Hyperf\JsonRpc;

use Closure;
use Hyperf\Rpc\ProtocolManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * {@inheritdoc}
 */
class CoreMiddleware extends \Hyperf\RpcServer\CoreMiddleware
{
    /**
     * @var \Hyperf\Rpc\ProtocolManager
     */
    protected $protocolManager;

    /**
     * @var \Hyperf\Rpc\Contract\DataFormatterInterface
     */
    protected $dataFormatter;

    /**
     * @var \Hyperf\Rpc\Contract\PackerInterface
     */
    protected $packer;

    /**
     * @var \Hyperf\JsonRpc\ResponseBuilder
     */
    protected $responseBuilder;

    /**
     * @var string
     */
    protected $protocol = 'jsonrpc';

    public function __construct(ContainerInterface $container, string $serverName)
    {
        parent::__construct($container, $serverName);
        $this->protocolManager = $container->get(ProtocolManager::class);
        $this->dataFormatter = $container->get($this->protocolManager->getDataFormatter($this->protocol));
        $this->packer = $container->get($this->protocolManager->getPacker($this->protocol));
        $this->responseBuilder = make(ResponseBuilder::class, [
            'dataFormatter' => $this->dataFormatter,
            'packer' => $this->packer,
        ]);
    }

    protected function handleFound(array $routes, ServerRequestInterface $request)
    {
        if ($routes[1] instanceof Closure) {
            $response = call($routes[1]);
        } else {
            [$controller, $action] = $this->prepareHandler($routes[1]);
            $controllerInstance = $this->container->get($controller);
            if (! method_exists($controller, $action)) {
                // Route found, but the handler does not exist.
                return $this->responseBuilder->buildErrorResponse($request, -32603);
            }
            $parameters = $this->parseParameters($controller, $action, $request->getParsedBody());
            try {
                $response = $controllerInstance->{$action}(...$parameters);
            } catch (\Exception $e) {
                return $this->responseBuilder->buildErrorResponse($request, 0, $e);
            }
        }
        return $response;
    }

    protected function handleNotFound(ServerRequestInterface $request)
    {
        return $this->responseBuilder->buildErrorResponse($request, -32601);
    }

    protected function handleMethodNotAllowed(array $routes, ServerRequestInterface $request)
    {
        return $this->handleNotFound($request);
    }

    protected function transferToResponse($response, ServerRequestInterface $request): ResponseInterface
    {
        return $this->responseBuilder->buildResponse($request, $response);
    }
}
