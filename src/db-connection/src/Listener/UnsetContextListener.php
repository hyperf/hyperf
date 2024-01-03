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
use Hyperf\DbConnection\ConnectionResolver;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;

use function array_keys;
use function array_walk;

class UnsetContextListener implements ListenerInterface
{
    public function __construct(private readonly ConfigInterface $config)
    {
    }

    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
        ];
    }

    public function process(object $event): void
    {
        /** @var BeforeWorkerStart $event */
        if (! $event->server->taskworker) {
            return;
        }
        $databases = array_keys($this->config->get('databases', []));
        array_walk($databases, fn (string $name) => Context::destroy(ConnectionResolver::getContextKey($name)));
    }
}
