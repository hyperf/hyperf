<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\Framework;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Framework\Annotation\Command;
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
        $annotationCommands = AnnotationCollector::getClassByAnnotation(Command::class);
        $annotationCommands = array_keys($annotationCommands);
        $commands = array_unique(array_merge($commands, $annotationCommands));
        $application = new Application();
        foreach ($commands as $command) {
            $application->add($container->get($command));
        }
        return $application;
    }
}
