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

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Paginator\Paginator;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Psr\Http\Message\ServerRequestInterface;

class PathResolverListener implements ListenerInterface
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
        Paginator::currentPathResolver(function ($default = '/') {
            if (! ApplicationContext::hasContainer() ||
                ! interface_exists(RequestInterface::class) ||
                ! Context::has(ServerRequestInterface::class)
            ) {
                return $default;
            }

            $container = ApplicationContext::getContainer();
            $url = $container->get(RequestInterface::class)->fullUrl();

            if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
                return $url;
            }

            return $url;
        });
    }
}
