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
use Hyperf\Command\Parser;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Framework\Event\BootApplication;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException as SymfonyInvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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

        if ($config->get('symfony.event.enable', false) && isset($eventDispatcher) && class_exists(SymfonyEventDispatcher::class)) {
            $application->setDispatcher(new SymfonyEventDispatcher($eventDispatcher));
        }

        foreach ($commands as $command) {
            $application->add(
                $this->pendingCommand($container->get($command))
            );
        }

        return $application;
    }

    /**
     * @throws InvalidArgumentException
     * @throws SymfonyInvalidArgumentException
     * @throws LogicException
     */
    protected function pendingCommand(SymfonyCommand $command): SymfonyCommand
    {
        /** @var null|Command $annotation */
        $annotation = AnnotationCollector::getClassAnnotation($command::class, Command::class) ?? null;

        if (! $annotation) {
            return $command;
        }

        if ($annotation->signature) {
            [$name, $arguments, $options] = Parser::parse($annotation->signature);
            if ($name) {
                $annotation->name = $name;
            }
            if ($arguments) {
                $annotation->arguments = $arguments;
            }
            if ($options) {
                $annotation->options = $options;
            }
        }

        if ($annotation->name) {
            $command->setName($annotation->name);
        }

        if ($annotation->arguments) {
            array_map(static function ($argument) use ($command) {
                if ($argument instanceof InputArgument) {
                    $command->getDefinition()->addArgument($argument);
                } elseif (is_array($argument)) {
                    $command->addArgument(...$argument);
                } else {
                    throw new LogicException(sprintf('Invalid argument type: %s.', gettype($argument)));
                }
            }, $annotation->arguments);
        }

        if ($annotation->options) {
            array_map(static function ($option) use ($command) {
                if ($option instanceof InputOption) {
                    $command->getDefinition()->addOption($option);
                } elseif (is_array($option)) {
                    $command->addOption(...$option);
                } else {
                    throw new LogicException(sprintf('Invalid option type: %s.', gettype($option)));
                }
            }, $annotation->options);
        }

        if ($annotation->description) {
            $command->setDescription($annotation->description);
        }

        if ($annotation->aliases) {
            $command->setAliases($annotation->aliases);
        }

        return $command;
    }
}
