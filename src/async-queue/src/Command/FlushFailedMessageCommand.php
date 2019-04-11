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
    public function __construct()
    {
        parent::__construct('queue:flush');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO: delete all failed queue.
    }

    protected function configure()
    {
        $this->setDescription('Delete all failed message from failed queue.');
        $this->addArgument('name', InputArgument::OPTIONAL, 'The name of queue.', 'default');
    }
}
