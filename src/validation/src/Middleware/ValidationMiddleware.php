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

namespace Hyperf\Validation\Middleware;

use FastRoute\Dispatcher;
use Hyperf\Di\ReflectionManager;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Server\Exception\ServerException;
use Hyperf\Utils\Context;
use Hyperf\Validation\Contract\ValidatesWhenResolved;
use Hyperf\Validation\UnauthorizedException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ValidationMiddleware implements MiddlewareInterface
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $implements = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Dispatched $dispatched */
        $dispatched = $request->getAttribute(Dispatched::class);

        if (! $dispatched instanceof Dispatched) {
            throw new ServerException(sprintf('The dispatched object is not a %s object.', Dispatched::class));
        }
        if ($dispatched->status !== Dispatcher::FOUND) {
            return $handler->handle($request);
        }

        $reflectionMethod = ReflectionManager::reflectMethod(...$dispatched->handler->callback);
        $parmeters = $reflectionMethod->getParameters();
        try {
            foreach ($parmeters as $parameter) {
                $classname = $parameter->getType()->getName();
                $implements = $this->getClassImplements($classname);
                if (in_array(ValidatesWhenResolved::class, $implements, true)) {
                    /** @var \Hyperf\Validation\Contract\ValidatesWhenResolved $parameterInstance */
                    $parameterInstance = $this->container->get($classname);
                    $parameterInstance->validateResolved();
                }
            }
        } catch (UnauthorizedException $exception) {
            $response = Context::override(ResponseInterface::class, function (ResponseInterface $response) {
                return $response->withStatus(403);
            });
            return $response;
        }

        return $handler->handle($request);
    }

    public function getClassImplements(string $class): array
    {
        if (! isset($this->implements[$class])) {
            $this->implements[$class] = class_implements($class);
        }
        return $this->implements[$class];
    }
}
