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
namespace Hyperf\Crontab\Command;

use Carbon\Carbon;
use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Crontab\Event\CrontabDispatcherStarted;
use Hyperf\Crontab\Scheduler;
use Hyperf\Crontab\Strategy\Executor;
use Hyperf\Nacos\Exception\InvalidArgumentException;
use Hyperf\Utils\Coroutine;
use Psr\Container\ContainerInterface;

class RunCommand extends Command
{
    protected Scheduler $scheduler;

    protected Executor $executor;

    protected ConfigInterface $config;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('crontab:run');
    }

    public function handle()
    {
        $this->scheduler = $this->container->get(Scheduler::class);
        $this->executor = $this->container->get(Executor::class);
        $this->config = $this->container->get(ConfigInterface::class);

        if ($this->config->get('crontab.enable', false)) {
            throw new InvalidArgumentException('Crontab is already disabled, please enable it first.');
        }

        $this->eventDispatcher?->dispatch(new CrontabDispatcherStarted());

        $this->line('Triggering Crontab', 'info');

        $crontabs = $this->scheduler->schedule();
        while (! $crontabs->isEmpty()) {
            $crontab = $crontabs->dequeue();
            Coroutine::create(function () use ($crontab) {
                if ($crontab->getExecuteTime() instanceof Carbon) {
                    $wait = $crontab->getExecuteTime()->getTimeStamp() - time();
                    Coroutine::sleep($wait);
                    $this->executor->execute($crontab);
                }
            });
        }
    }
}
