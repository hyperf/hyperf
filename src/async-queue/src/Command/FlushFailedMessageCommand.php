<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.org
 * @document https://wiki.hyperf.org
 * @contact  group@hyperf.org
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\AsyncQueue\Command;

use Hyperf\AsyncQueue\Driver\DriverFactory;
use Hyperf\Framework\Annotation\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @Command
 */
class FlushFailedMessageCommand extends SymfonyCommand
{
    /**
     * @var DriverFactory
     */
    protected $factory;

    public function __construct(DriverFactory $factory)
    {
        $this->factory = $factory;
        parent::__construct('queue:flush');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');

        $driver = $this->factory->get($name);

        $driver->flush();

        $output->writeln('<fg=red>Flush all message from failed queue.</>');
    }

    protected function configure()
    {
        $this->setDescription('Delete all message from failed queue.');
        $this->addArgument('name', InputArgument::OPTIONAL, 'The name of queue.', 'default');
    }
}
