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

namespace Hyperf\Framework;

use Hyperf\Contract\ConfigInterface;
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
