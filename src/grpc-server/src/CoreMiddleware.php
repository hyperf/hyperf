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

namespace Hyperf\GrpcServer;

use FastRoute\Dispatcher;
use Google\Protobuf\Internal\Message;
use Google\Protobuf\Internal\Message as ProtobufMessage;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\GrpcServer\Router\Dispatcher as GrpcDispatcher;
use Hyperf\GrpcServer\Utils\Parser;
use Hyperf\HttpServer\CoreMiddleware as HttpCoreMiddleware;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Swoft\Http\Message\Stream\SwooleStream;

class CoreMiddleware extends HttpCoreMiddleware
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dispatcher = $container->get(GrpcDispatcher::class);
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var ResponseInterface $response */
        $uri = $request->getUri();

        /**
         * @var array $routes
         * Returns array with one of the following formats:
         *     [self::NOT_FOUND]
         *     [self::METHOD_NOT_ALLOWED, ['GET', 'OTHER_ALLOWED_METHODS']]
         *     [self::FOUND, $handler, ['varName' => 'value', ...]]
         */
        $routes = $this->dispatcher->dispatch($request->getMethod(), $uri->getPath());
        switch ($routes[0]) {
            case Dispatcher::FOUND:
                [$controller, $action] = $this->prepareHandler($routes[1]);
                $controllerInstance = $this->container->get($controller);
                if (!method_exists($controller, $action)) {
                    $grpcMessage = sprintf('%s:%s is not implemented!', $controller, $action);
                    return $this->handleResponse(null, 500, '500', $grpcMessage);
                }
                $parameters = $this->parseParameters($controller, $action, $routes[2]);
                $result = $controllerInstance->$action(...$parameters);
                if (!$result instanceof Message) {
                    $grpcMessage = 'The result is not a valid message!';
                    return $this->handleResponse(null, 500, '500', $grpcMessage);
                }

                return $this->handleResponse($result, 200);

            case Dispatcher::NOT_FOUND:
            case Dispatcher::METHOD_NOT_ALLOWED:
            default:
                return $this->handleResponse(null, 404, '404', 'Route Not Find!');
        }
    }

    /**
     * Transfer the non-standard response content to a standard response object.
     *
     * @param string|array $response
     */
    protected function transferToResponse($response): ResponseInterface
    {
        if ($response instanceof Message) {
            $body = Parser::serializeMessage($response);
            $response = $this->response()
                ->withAddedHeader('Content-Type', 'application/grpc')
                ->withAddedHeader('trailer', 'grpc-status, grpc-message')
                ->withBody(new SwooleStream($body));

            $response->getSwooleResponse()->trailer('grpc-status', '0');
            $response->getSwooleResponse()->trailer('grpc-message', '');

            return $response;
        }

        if (is_string($response)) {
            return $this->response()->withBody(new SwooleStream($response));
        }

        if (is_array($response)) {
            return $this->response()
                ->withAddedHeader('Content-Type', 'application/json')
                ->withBody(new SwooleStream(json_encode($response)));
        }

        return $this->response()->withBody(new SwooleStream((string)$response));
    }

    protected function parseParameters(string $controller, string $action, array $arguments): array
    {
        $injections = [];
        $definitions = MethodDefinitionCollector::getOrParse($controller, $action);


        foreach ($definitions ?? [] as $definition) {
            if (!is_array($definition)) {
                throw new \RuntimeException('Invalid method definition.');
            }
            if (!isset($definition['type']) || !isset($definition['name'])) {
                $injections[] = null;
                continue;
            }
            $injections[] = value(function () use ($definition, $arguments) {
                switch ($definition['type']) {
                    case 'object':
                        $ref = $definition['ref'];
                        $class = ReflectionManager::reflectClass($ref);
                        $parentClass = $class->getParentClass();
                        if ($parentClass->getName() === ProtobufMessage::class) {
                            $request = $this->request();
                            $stream = $request->getBody();
                            return Parser::deserializeMessage([$class->getName(), null], $stream->getContents());
                        }

                        if (!$this->container->has($definition['ref']) && !$definition['allowsNull']) {
                            throw new \RuntimeException(sprintf('Argument %s invalid, object %s not found.', $definition['name'], $definition['ref']));
                        }

                        return $this->container->get($definition['ref']);
                    default:
                        throw new \RuntimeException('Invalid method definition detected.');
                }
            });
        }

        return $injections;
    }

    /**
     * @return RequestInterface
     */
    protected function request()
    {
        return Context::get(ServerRequestInterface::class);
    }

    /**
     * Handle GRPC Response
     * @param ProtobufMessage|null $message
     * @param int $httpStatus
     * @param string $grpcStatus
     * @param string $grpcMessage
     * @return ResponseInterface
     */
    protected function handleResponse(?Message $message, $httpStatus = 200, string $grpcStatus = '0', string $grpcMessage = ''): ResponseInterface
    {
        $response = $this->response()->withStatus($httpStatus)
            ->withBody(new SwooleStream(Parser::serializeMessage($message)))
            ->withAddedHeader('Server', 'Hyperf')
            ->withAddedHeader('Content-Type', 'application/grpc')
            ->withAddedHeader('trailer', 'grpc-status, grpc-message');

        $response->getSwooleResponse()->trailer('grpc-status', $grpcStatus);
        $response->getSwooleResponse()->trailer('grpc-message', $grpcMessage);

        return $response;
    }
}
