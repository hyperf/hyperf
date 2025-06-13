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

use Hyperf\Command\Annotation\AsCommand as AsCommandAnnotation;
use Hyperf\Command\AsCommand;
use Hyperf\Command\Console;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\ContainerInterface as ContainerPlusInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\MultipleAnnotation;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Psr\Container\ContainerInterface;

class RegisterCommandListener implements ListenerInterface
{
    /**
     * @param ContainerPlusInterface $container
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
        if (! $this->container instanceof ContainerPlusInterface) {
            $this->logger->error(sprintf('[command] Commands registered failed, because the container cannot implements %s', ContainerPlusInterface::class));
            return;
        }

        $this->registerClosureCommands();
        $this->registerAnnotationCommands();

        $this->logger->debug(sprintf('[command] Commands registered by %s', self::class));
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
        $commands = AnnotationCollector::getMethodsByAnnotation(AsCommandAnnotation::class);

        foreach ($commands as $metadata) {
            /** @var MultipleAnnotation $multiAnnotation */
            $multiAnnotation = $metadata['annotation'];
            /** @var AsCommandAnnotation[] $annotations */
            $annotations = $multiAnnotation->toAnnotations();
            foreach ($annotations as $annotation) {
                $command = new AsCommand(
                    $this->container,
                    $annotation->signature,
                    $metadata['class'],
                    $metadata['method'],
                );

                if ($annotation->description) {
                    $command->setDescription($annotation->description);
                }
                if ($annotation->aliases) {
                    $command->setAliases($annotation->aliases);
                }

                $this->container->set($annotation->id, $command);
                $this->appendConfig('commands', $annotation->id);
            }
        }
    }

    private function appendConfig(string $key, $configValues): void
    {
        $configs = $this->config->get($key, []);
        $configs[] = $configValues;
        $this->config->set($key, $configs);
    }
}
