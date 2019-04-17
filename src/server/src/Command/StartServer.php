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

namespace Hyperf\Server\Command;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Framework\Annotation\Command;
use Hyperf\Server\ServerFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @Command
 */
class StartServer extends SymfonyCommand
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct('start');
        $this->container = $container;

        $this->setDescription('Start swoole server.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkEnv($output);

        $factory = $this->container->get(ServerFactory::class);
        $config = $this->container->get(ConfigInterface::class)->get('server');

        $factory->configure($config);
        $factory->start();
    }

    private function checkEnv(OutputInterface $output)
    {
        if (ini_get_all('swoole')['swoole.use_shortname']['local_value'] !== 'Off') {
            $output->writeln('<error>ERROR</error> Swoole short name have to disable before start server, please set swoole.use_shortname = \'Off\' into your php.ini.');
            exit(0);
        }
    }
}
