<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @author   liuuniverse@139.com
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Server\Command;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Swoole\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReloadServer extends Command
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct('reload');
        $this->setDescription('Reload hyperf servers means restarting all worker and task-worker process');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->container->get(ConfigInterface::class);
        $file = $config->get('server.settings.pid_file',BASE_PATH . '/runtime/hyperf.pid');
        if(file_exists($file) && $pid = intval(file_get_contents($file))){
            if(Process::kill($pid,0) && Process::kill($pid,SIGUSR1)){
                $this->container->get(StdoutLoggerInterface::class)->info(sprintf("Reload Server Pid %d SUCCESS.",$pid));
                return 0;
            }
        }
        throw new InvalidArgumentException('Reload Server ERROR.');
    }

}
