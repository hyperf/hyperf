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

namespace Hyperf\DbConnection\Listener;

use Hyperf\Context\Context;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Database\ConnectionResolverInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Psr\Container\ContainerInterface;

class UnsetContextInTaskWorkerListener implements ListenerInterface
{
    public function __construct(
        private ContainerInterface $container,
        private ConfigInterface $config
    ) {
    }

    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
        ];
    }

    /**
     * @param BeforeWorkerStart $event
     */
    public function process(object $event): void
    {
        if (! $event instanceof BeforeWorkerStart || ! $event->server->taskworker) {
            return;
        }

        $connectionResolver = $this->container->get(ConnectionResolverInterface::class);
        $databases = (array) $this->config->get('databases', []);

        foreach ($databases as $name => $_) {
            $contextKey = (fn () => $this->getContextKey($name))->call($connectionResolver);
            Context::destroy($contextKey);
        }
    }
}
