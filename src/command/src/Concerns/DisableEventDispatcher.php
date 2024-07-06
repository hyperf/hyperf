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

namespace Hyperf\Command\Concerns;

use Hyperf\Context\ApplicationContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

trait DisableEventDispatcher
{
    public function addDisableDispatcherOption(): void
    {
        $this->addOption('disable-event-dispatcher', null, InputOption::VALUE_NONE, 'Whether disable event dispatcher.');
    }

    public function disableDispatcher(InputInterface $input)
    {
        if (! $input->getOption('disable-event-dispatcher')) {
            if (! ApplicationContext::hasContainer()) {
                return;
            }

            $container = ApplicationContext::getContainer();
            if (! $container->has(EventDispatcherInterface::class)) {
                return;
            }

            $dispatcher = $container->get(EventDispatcherInterface::class);

            $this->eventDispatcher = $dispatcher instanceof EventDispatcherInterface ? $dispatcher : null;
        }
    }
}
