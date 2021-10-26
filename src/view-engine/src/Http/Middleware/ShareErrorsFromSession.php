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

use Hyperf\Contract\SessionInterface;
use Hyperf\ViewEngine\Contract\FactoryInterface;
use Hyperf\ViewEngine\ViewErrorBag;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ShareErrorsFromSession implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var FactoryInterface
     */
    protected $view;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->session = $container->get(SessionInterface::class);
        $this->view = $container->get(FactoryInterface::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var ViewErrorBag $errors */
        $errors = $this->session->get('errors') ?: new ViewErrorBag();

        $this->view->share('errors', $errors);

        return $handler->handle($request);
    }
}
