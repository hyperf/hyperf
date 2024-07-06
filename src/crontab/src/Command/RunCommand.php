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

use Hyperf\Command\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Crontab\Crontab;
use Hyperf\Crontab\Event\CrontabDispatcherStarted;
use Hyperf\Crontab\Exception\InvalidArgumentException;
use Hyperf\Crontab\Scheduler;
use Hyperf\Crontab\Strategy\Executor;
use Psr\Container\ContainerInterface;

class RunCommand extends Command
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('crontab:run');
    }

    public function handle()
    {
        $config = $this->container->get(ConfigInterface::class);
        $scheduler = $this->container->get(Scheduler::class);
        $executor = $this->container->get(Executor::class);

        if (! $config->get('crontab.enable', false)) {
            throw new InvalidArgumentException('Crontab is already disabled, please enable it first.');
        }

        $this->eventDispatcher?->dispatch(new CrontabDispatcherStarted());

        $this->line('Triggering Crontab', 'info');

        /** @var Crontab[] $crontabs */
        $crontabs = $scheduler->schedule();

        foreach ($crontabs as $crontab) {
            $executor->execute($crontab);
        }

        foreach ($crontabs as $crontab) {
            $crontab->wait();
        }
    }
}
