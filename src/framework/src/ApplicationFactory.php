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
namespace Hyperf\Framework;

use Hyperf\Command\Annotation\Command;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Framework\Event\BootApplication;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Application;

class ApplicationFactory
{
    public function __invoke(ContainerInterface $container)
    {
        if ($container->has(EventDispatcherInterface::class)) {
            $eventDispatcher = $container->get(EventDispatcherInterface::class);
            $eventDispatcher->dispatch(new BootApplication());
        }

        $config = $container->get(ConfigInterface::class);
        $commands = $config->get('commands', []);
        // Append commands that defined by annotation.
        $annotationCommands = [];
        if (class_exists(AnnotationCollector::class) && class_exists(Command::class)) {
            $annotationCommands = AnnotationCollector::getClassesByAnnotation(Command::class);
            $annotationCommands = array_keys($annotationCommands);
        }

        $commands = array_unique(array_merge($commands, $annotationCommands));
        $application = new Application();

        if (isset($eventDispatcher) && class_exists(SymfonyEventDispatcher::class)) {
            $application->setDispatcher(new SymfonyEventDispatcher($eventDispatcher));
        }

        foreach ($commands as $command) {
            $application->add($container->get($command));
        }
        return $application;
    }
}
