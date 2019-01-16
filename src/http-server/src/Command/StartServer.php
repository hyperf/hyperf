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

namespace Hyperf\HttpServer\Command;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Framework\Server;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartServer extends Command
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var Server
     */
    private $server;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct('start');
        $this->container = $container;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkEnv($output);
        $this->initServer();
        $this->server->run();
    }

    private function initServer()
    {
        $config = $this->container->get(ConfigInterface::class);
        $serverConfigs = $config->get('servers', []);
        if (! $serverConfigs) {
            throw new \InvalidArgumentException('No available server.');
        }

        $this->server = $this->container->get(Server::class)->initConfigs($serverConfigs);
    }

    private function checkEnv(OutputInterface $output)
    {
        if (ini_get_all('swoole')['swoole.use_shortname']['local_value'] !== 'Off') {
            $output->writeln('<error>ERROR</error> Swoole short name have to disable before start server, please set swoole.use_shortname = \'Off\' into your php.ini.');
            exit(0);
        }
    }
}
