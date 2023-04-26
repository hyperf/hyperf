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
namespace Hyperf\Signal\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Process\Event\BeforeProcessHandle;
use Hyperf\Server\Event\MainCoroutineServerStart;
use Hyperf\Signal\SignalHandlerInterface as SignalHandler;
use Hyperf\Signal\SignalManager;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\value;

class SignalRegisterListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            BeforeWorkerStart::class,
            BeforeProcessHandle::class,
            MainCoroutineServerStart::class,
        ];
    }

    public function process(object $event): void
    {
        $manager = $this->container->get(SignalManager::class);

        $manager->init();
        $manager->listen(value(function () use ($event) {
            if ($event instanceof BeforeWorkerStart || $event instanceof MainCoroutineServerStart) {
                return SignalHandler::WORKER;
            }

            if ($event instanceof BeforeProcessHandle) {
                return SignalHandler::PROCESS;
            }

            return null;
        }));
    }
}
