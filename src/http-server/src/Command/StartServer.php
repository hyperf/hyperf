<?php

namespace Hyperf\HttpServer\Command;


use Hyperf\Framework\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

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

    /**
     * @var Scanner
     */
    private $scanner;

    public function __construct(ContainerInterface $container, Scanner $scanner)
    {
        parent::__construct('start');
        $this->container = $container;
        $this->scanner = $scanner;
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