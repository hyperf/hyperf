<?php

namespace Hyperflex\Framework;

use Hyperflex\Framework\Contracts\ConfigInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

class ApplicationFactory
{

    /**
     * Define the default commands here.
     *
     * @var array
     */
    private $defaultCommands = [];

    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $commands = $config->get('commands', []);
        $commands = array_replace($this->defaultCommands, $commands);
        $application = new Application();
        foreach ($commands as $command) {
            $application->add($container->get($command));
        }
        return $application;
    }

}