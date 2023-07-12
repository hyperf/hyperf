<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Command\Listener;

use Hyperf\Command\Annotation\AsCommandCollector;
use Hyperf\Command\AsCommand;
use Hyperf\Command\Console;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Psr\Container\ContainerInterface;

class RegisterCommandListener implements ListenerInterface
{
    /**
     * @param \Hyperf\Di\Container $container
     */
    public function __construct(private ContainerInterface $container, private ConfigInterface $config, private StdoutLoggerInterface $logger)
    {
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    public function process(object $event): void
    {
        $this->registerClosureCommands();
        $this->registerAnnotationCommands();

        $this->logger->debug(sprintf('[closure-command] Commands registered by %s', self::class));
    }

    private function registerClosureCommands(): void
    {
        $route = Console::ROUTE;

        if (! file_exists($route)) {
            return;
        }

        require_once $route;

        foreach (Console::getCommands() as $handlerId => $command) {
            $this->container->set($handlerId, $command);
            $this->appendConfig('commands', $handlerId);
        }
    }

    private function registerAnnotationCommands(): void
    {
        $commands = AsCommandCollector::list();

        foreach ($commands as $commandId => $metadata) {
            $command = new AsCommand(
                $this->container,
                $metadata['signature'],
                $metadata['class'],
                $metadata['method'],
                $metadata['description'] ?? ''
            );

            $this->container->set($commandId, $command);
            $this->appendConfig('commands', $commandId);
        }
    }

    private function appendConfig(string $key, $configValues): void
    {
        $configs = $this->config->get($key, []);
        array_push($configs, $configValues);
        $this->config->set($key, $configs);
    }
}
