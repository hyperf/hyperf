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
namespace Hyperf\HttpServerRoute;

use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\HttpServerRoute\Exception\RequestNotFoundException;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;

class RouteContext
{
    public function getRouteName(): string
    {
        $dispatched = $this->getRequest()->getAttribute(Dispatched::class);
        if (! $dispatched instanceof Dispatched) {
            throw new RequestNotFoundException('Request is invalid.');
        }

        $handler = $dispatched->handler;
        return $handler->options['name'] ?? $handler->route;
    }

    protected function getRequest(): ServerRequestInterface
    {
        $request = Context::get(ServerRequestInterface::class);
        if (! $request instanceof ServerRequestInterface) {
            throw new RequestNotFoundException('Request is not found.');
        }
        return $request;
    }
}
