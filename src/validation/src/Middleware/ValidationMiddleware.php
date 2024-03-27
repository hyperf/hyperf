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

namespace Hyperf\Validation\Middleware;

use Closure;
use FastRoute\Dispatcher;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\MultipleAnnotation;
use Hyperf\Di\ReflectionManager;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Server\Exception\ServerException;
use Hyperf\Validation\Annotation\Scene;
use Hyperf\Validation\Contract\ValidatesWhenResolved;
use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\UnauthorizedException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

class ValidationMiddleware implements MiddlewareInterface
{
    private array $implements = [];

    public function __construct(private ContainerInterface $container)
    {
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

        Context::set(ServerRequestInterface::class, $request);

        if ($this->shouldHandle($dispatched)) {
            try {
                [$requestHandler, $method] = $this->prepareHandler($dispatched->handler->callback);
                if ($method) {
                    $reflectionMethod = ReflectionManager::reflectMethod($requestHandler, $method);
                    $parameters = $reflectionMethod->getParameters();
                    foreach ($parameters as $parameter) {
                        if ($parameter->getType() === null) {
                            continue;
                        }
                        $className = $parameter->getType()->getName();
                        if ($this->isImplementedValidatesWhenResolved($className)) {
                            /** @var ValidatesWhenResolved $formRequest */
                            $formRequest = $this->container->get($className);
                            if ($formRequest instanceof FormRequest) {
                                $this->handleSceneAnnotation($formRequest, $requestHandler, $method, $parameter->getName());
                            }
                            $formRequest->validateResolved();
                        }
                    }
                }
            } catch (UnauthorizedException $exception) {
                return $this->handleUnauthorizedException($exception);
            }
        }

        return $handler->handle($request);
    }

    public function isImplementedValidatesWhenResolved(string $className): bool
    {
        if (! isset($this->implements[$className]) && class_exists($className)) {
            $implements = class_implements($className);
            $this->implements[$className] = in_array(ValidatesWhenResolved::class, $implements, true);
        }
        return $this->implements[$className] ?? false;
    }

    protected function handleSceneAnnotation(FormRequest $request, string $class, string $method, string $argument): void
    {
        /** @var null|MultipleAnnotation $scene */
        $scene = AnnotationCollector::getClassMethodAnnotation($class, $method)[Scene::class] ?? null;
        if (! $scene) {
            return;
        }

        $annotations = $scene->toAnnotations();
        if (empty($annotations)) {
            return;
        }

        /** @var Scene $annotation */
        foreach ($annotations as $annotation) {
            if ($annotation->argument === null || $annotation->argument === $argument) {
                $request->scene($annotation->scene ?? $method);
                return;
            }
        }
    }

    /**
     * @param UnauthorizedException $exception Keep this argument here even this argument is unused in the method,
     *                                         maybe the user need the details of exception when rewrite this method
     */
    protected function handleUnauthorizedException(UnauthorizedException $exception): ResponseInterface
    {
        return Context::override(ResponseInterface::class, fn (ResponseInterface $response) => $response->withStatus(403));
    }

    protected function shouldHandle(Dispatched $dispatched): bool
    {
        return $dispatched->status === Dispatcher::FOUND && ! $dispatched->handler->callback instanceof Closure;
    }

    /**
     * @see \Hyperf\HttpServer\CoreMiddleware::prepareHandler()
     */
    protected function prepareHandler(array|string $handler): array
    {
        if (is_string($handler)) {
            if (str_contains($handler, '@')) {
                return explode('@', $handler);
            }
            $array = explode('::', $handler);
            if (! isset($array[1]) && class_exists($handler) && method_exists($handler, '__invoke')) {
                $array[1] = '__invoke';
            }
            return [$array[0], $array[1] ?? null];
        }
        if (is_array($handler) && isset($handler[0], $handler[1])) {
            return $handler;
        }
        throw new RuntimeException('Handler not exist.');
    }
}
