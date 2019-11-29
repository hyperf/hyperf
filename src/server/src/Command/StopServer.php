<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Server\Command;

use Swoole\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @\Hyperf\Command\Annotation\Command()
 */
class StopServer extends Command
{
    public function __construct()
    {
        parent::__construct('stop');
    }

    protected function configure()
    {
        $this->setDescription('Stop hyperf servers.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $pidFile = BASE_PATH . '/runtime/hyperf.pid';
        $pid = file_exists($pidFile) ? intval(file_get_contents($pidFile)) : false;
        if (!$pid) {
            $io->note('swoole server pid is invalid.');
            return false;
        }

        if (!Process::kill($pid, SIG_DFL)) {
            $io->note('swoole server process does not exist.');
            return false;
        }

        if (!Process::kill($pid, SIGTERM)) {
            $io->error('swoole server stop error.');
            return false;
        }

        $io->success('swoole server stop success.');
    }

}
