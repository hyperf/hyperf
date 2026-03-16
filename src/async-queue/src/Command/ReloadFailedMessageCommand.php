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

namespace Hyperf\AsyncQueue\Command;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ReloadFailedMessageCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('queue:reload');
    }

    public function handle()
    {
        $name = $this->input->getArgument('name');
        $queue = $this->input->getOption('queue');

        $factory = $this->container->get(DriverFactory::class);
        $driver = $factory->get($name);

        $num = $driver->reload($queue);

        $this->output->writeln(sprintf('<fg=green>Reload %d failed message into waiting queue.</>', $num));
    }

    protected function configure()
    {
        $this->setDescription('Reload all failed message into waiting queue.');
        $this->addArgument('name', InputArgument::OPTIONAL, 'The name of queue.', 'default');
        $this->addOption('queue', 'Q', InputOption::VALUE_OPTIONAL, 'The channel name of queue.');
    }
}
