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
namespace Hyperf\GrpcServer;

use Closure;
use FastRoute\Dispatcher;
use Google\Protobuf\Internal\Message;
use Google\Protobuf\Internal\Message as ProtobufMessage;
use Hyperf\Context\Context;
use Hyperf\Di\MethodDefinitionCollector;
use Hyperf\Di\ReflectionManager;
use Hyperf\Grpc\Parser;
use Hyperf\Grpc\StatusCode;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\CoreMiddleware as HttpCoreMiddleware;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Server\Exception\ServerException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class CoreMiddleware extends HttpCoreMiddleware
{
    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = Context::set(ServerRequestInterface::class, $request);

        /** @var Dispatched $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);

        if (! $dispatched instanceof Dispatched) {
            throw new ServerException(sprintf('The dispatched object is not a %s object.', Dispatched::class));
        }

        switch ($dispatched->status) {
            case Dispatcher::FOUND:
                if ($dispatched->handler->callback instanceof Closure) {
                    $parameters = $this->parseClosureParameters($dispatched->handler->callback, $dispatched->params);
                    $callback = $dispatched->handler->callback;
                    $result = $callback(...$parameters);
                } else {
                    [$controller, $action] = $this->prepareHandler($dispatched->handler->callback);
                    $controllerInstance = $this->container->get($controller);
                    if (! method_exists($controller, $action)) {
                        $grpcMessage = 'Action not exist.';
                        return $this->handleResponse(null, 200, StatusCode::INTERNAL, $grpcMessage);
                    }
                    $parameters = $this->parseMethodParameters($controller, $action, $dispatched->params);
                    $result = $controllerInstance->{$action}(...$parameters);
                }

                if (! $result instanceof Message) {
                    $grpcMessage = 'The result is not a valid message.';
                    return $this->handleResponse(null, 200, StatusCode::INTERNAL, $grpcMessage);
                }

                return $this->handleResponse($result, 200);
            case Dispatcher::NOT_FOUND:
            case Dispatcher::METHOD_NOT_ALLOWED:
            default:
                return $this->handleResponse(null, 200, StatusCode::NOT_FOUND, 'Route Not Found.');
        }
    }

    /**
     * Transfer the non-standard response content to a standard response object.
     *
     * @param array|string $response
     */
    protected function transferToResponse($response, ServerRequestInterface $request): ResponseInterface
    {
        if ($response instanceof Message) {
            $body = Parser::serializeMessage($response);
            return $this->response()
                ->withAddedHeader('Content-Type', 'application/grpc')
                ->withAddedHeader('trailer', 'grpc-status, grpc-message')
                ->withBody(new SwooleStream($body))
                ->withTrailer('grpc-status', '0')
                ->withTrailer('grpc-message', '');
        }

        if (is_string($response)) {
            return $this->response()->withBody(new SwooleStream($response));
        }

        if (is_array($response)) {
            return $this->response()
                ->withAddedHeader('Content-Type', 'application/json')
                ->withBody(new SwooleStream(json_encode($response)));
        }

        return $this->response()->withBody(new SwooleStream((string) $response));
    }

    protected function parseMethodParameters(string $controller, string $action, array $arguments): array
    {
        $injections = [];
        $definitions = MethodDefinitionCollector::getOrParse($controller, $action);

        foreach ($definitions ?? [] as $definition) {
            if (! is_array($definition)) {
                throw new RuntimeException('Invalid method definition.');
            }
            if (! isset($definition['type']) || ! isset($definition['name'])) {
                $injections[] = null;
                continue;
            }
            $injections[] = value(function () use ($definition) {
                switch ($definition['type']) {
                    case 'object':
                        $ref = $definition['ref'];
                        $class = ReflectionManager::reflectClass($ref);
                        $parentClass = $class->getParentClass();
                        if ($parentClass && $parentClass->getName() === ProtobufMessage::class) {
                            $request = $this->request();
                            $stream = $request->getBody();
                            return Parser::deserializeMessage([$class->getName(), null], (string) $stream);
                        }

                        if (! $this->container->has($definition['ref']) && ! $definition['allowsNull']) {
                            throw new RuntimeException(sprintf('Argument %s invalid, object %s not found.', $definition['name'], $definition['ref']));
                        }

                        return $this->container->get($definition['ref']);
                    default:
                        throw new RuntimeException('Invalid method definition detected.');
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
     * Handle GRPC Response.
     */
    protected function handleResponse(?Message $message, int $httpStatus = 200, int $grpcStatus = StatusCode::OK, string $grpcMessage = ''): ResponseInterface
    {
        return $this->response()->withStatus($httpStatus)
            ->withBody(new SwooleStream(Parser::serializeMessage($message)))
            ->withAddedHeader('Server', 'Hyperf')
            ->withAddedHeader('Content-Type', 'application/grpc')
            ->withAddedHeader('trailer', 'grpc-status, grpc-message')
            ->withTrailer('grpc-status', (string) $grpcStatus)
            ->withTrailer('grpc-message', $grpcMessage);
    }
}
