<?php

namespace Hyperf\Paginator\Listener;


use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Paginator\Paginator;
use Hyperf\Utils\ApplicationContext;

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
    public function process(object $event)
    {
        Paginator::currentPageResolver(function ($pageName = 'page') {
            if (! ApplicationContext::hasContainer() || ! interface_exists(RequestInterface::class)) {
                return 1;
            }
            $container = ApplicationContext::getContainer();
            $page = $container->get(RequestInterface::class)->input($pageName);

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }

            return 1;
        });
    }
}