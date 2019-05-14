<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\AsyncQueue\Command;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Framework\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @Command
 */
class ReloadFailedMessageCommand extends SymfonyCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('queue:reload');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $queue = $input->getOption('queue');

        $factory = $this->container->get(DriverFactory::class);
        $driver = $factory->get($name);

        $num = $driver->reload($queue);

        $output->writeln(sprintf('<fg=green>Reload %d failed message into waiting queue.</>', $num));
    }

    protected function configure()
    {
        $this->setDescription('Reload all failed message into waiting queue.');
        $this->addArgument('name', InputArgument::OPTIONAL, 'The name of queue.', 'default');
        $this->addOption('queue', 'Q', InputOption::VALUE_OPTIONAL, 'The channel name of queue.');
    }
}
