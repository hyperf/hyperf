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

namespace Hyperf\ViewEngine\Http\Middleware;

use Hyperf\Contract\MessageProvider;
use Hyperf\Contract\SessionInterface;
use Hyperf\Session\Session;
use Hyperf\Support\MessageBag;
use Hyperf\Validation\ValidationException;
use Hyperf\ViewEngine\Contract\FactoryInterface;
use Hyperf\ViewEngine\ViewErrorBag;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ValidationExceptionHandle implements MiddlewareInterface
{
    /**
     * @var Session
     */
    protected SessionInterface $session;

    protected FactoryInterface $view;

    public function __construct(protected ContainerInterface $container)
    {
        $this->session = $container->get(SessionInterface::class);
        $this->view = $container->get(FactoryInterface::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $response = $handler->handle($request);
        } catch (Throwable $throwable) {
            if ($throwable instanceof ValidationException) {
                /* @var ValidationException $throwable */
                $this->withErrors($throwable->errors(), $throwable->errorBag);
                return $this->response()->redirect($this->session->previousUrl());
            }

            throw $throwable;
        }

        return $response;
    }

    public function withErrors($provider, $key = 'default')
    {
        $value = $this->parseErrors($provider);

        $errors = $this->session->get('errors', new ViewErrorBag());

        if (! $errors instanceof ViewErrorBag) {
            $errors = new ViewErrorBag();
        }

        $this->session->flash(
            'errors',
            $errors->put($key, $value)
        );

        return $this;
    }

    protected function response()
    {
        return $this->container->get(\Hyperf\HttpServer\Contract\ResponseInterface::class);
    }

    protected function parseErrors($provider)
    {
        if ($provider instanceof MessageProvider) {
            return $provider->getMessageBag();
        }

        return new MessageBag((array) $provider);
    }
}
