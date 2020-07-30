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
namespace Hyperf\HttpServerRoute\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\HttpServerRoute\RouteCollector;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Psr\Container\ContainerInterface;

class InitRouteCollectorListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function listen(): array
    {
        return [
            MainCoroutineServerStart::class,
            BeforeWorkerStart::class,
        ];
    }

    public function process(object $event)
    {
        $this->container->get(RouteCollector::class);
    }
}
