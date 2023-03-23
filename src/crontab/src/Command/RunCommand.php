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
use Hyperf\Crontab\Scheduler;
use Hyperf\Crontab\Strategy\Executor;
use Hyperf\Nacos\Exception\InvalidArgumentException;
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

        /** @var Crontab[] $crontabs */
        $crontabs = $this->scheduler->schedule();
        foreach ($crontabs as $crontab) {
            $this->executor->execute($crontab);
        }
        foreach ($crontabs as $crontab) {
            $crontab->wait();
        }
    }
}
