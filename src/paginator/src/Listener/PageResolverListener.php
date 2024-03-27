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

namespace Hyperf\Paginator\Listener;

use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Paginator\Paginator;
use Psr\Http\Message\ServerRequestInterface;

class PageResolverListener implements ListenerInterface
{
    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event): void
    {
        Paginator::currentPageResolver(function ($pageName = 'page') {
            if (! ApplicationContext::hasContainer()
                || ! interface_exists(RequestInterface::class)
                || ! Context::has(ServerRequestInterface::class)
            ) {
                return 1;
            }

            $container = ApplicationContext::getContainer();
            $page = $container->get(RequestInterface::class)->input($pageName);

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }

            return 1;
        });

        Paginator::currentPathResolver(function () {
            $default = '/';
            if (! ApplicationContext::hasContainer()
                || ! interface_exists(RequestInterface::class)
                || ! Context::has(ServerRequestInterface::class)
            ) {
                return $default;
            }

            $container = ApplicationContext::getContainer();
            return $container->get(RequestInterface::class)->url();
        });
    }
}
