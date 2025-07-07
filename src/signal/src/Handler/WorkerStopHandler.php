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

namespace Hyperf\Signal\Handler;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Signal\SignalHandlerInterface;
use Psr\Container\ContainerInterface;
use Swoole;

class WorkerStopHandler implements SignalHandlerInterface
{
    protected ConfigInterface $config;

    public function __construct(protected ContainerInterface $container)
    {
        $this->config = $container->get(ConfigInterface::class);
    }

    public function listen(): array
    {
        return [
            [self::WORKER, SIGTERM],
            [self::WORKER, SIGINT],
        ];
    }

    public function handle(int $signal): void
    {
        if ($signal !== SIGINT) {
            $time = $this->config->get('server.settings.max_wait_time', 3);
            sleep($time);
        }

        // Prevents the display of deadlock error messages during worker shutdown.
        // These errors are purely costmetic in signal handlers and can be safely ignored.
        Swoole\Coroutine::set(['enable_deadlock_check' => false]);

        $this->container->get(Swoole\Server::class)->stop();
    }
}
