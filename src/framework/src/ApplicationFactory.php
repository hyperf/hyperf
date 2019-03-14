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
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Framework\Annotation\Command;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

class ApplicationFactory
{
    public function __invoke(ContainerInterface $container)
    {
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
