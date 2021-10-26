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
namespace Hyperf\Command;

use Hyperf\Utils\ApplicationContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

trait EnableEventDispatcher
{
    public function addEnableDispatcherOption()
    {
        $this->addOption('enable-event-dispatcher', null, InputOption::VALUE_NONE, 'Whether enable event dispatcher.');
    }

    public function enableDispatcher(InputInterface $input)
    {
        if ($input->getOption('enable-event-dispatcher')) {
            $this->eventDispatcher = ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
        }
    }
}
