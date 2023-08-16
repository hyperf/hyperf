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
                $annotation->arguments = array_merge($annotation->arguments, $arguments);
            }
            if ($options) {
                $annotation->options = array_merge($annotation->options, $options);
            }
        }

        if ($annotation->name) {
            $command->setName($annotation->name);
        }

        if ($annotation->arguments) {
            $annotation->arguments = array_map(static function ($argument): InputArgument {
                if ($argument instanceof InputArgument) {
                    return $argument;
                }

                if (is_array($argument)) {
                    return new InputArgument(...$argument);
                }

                throw new LogicException(sprintf('Invalid argument type: %s.', gettype($argument)));
            }, $annotation->arguments);

            $command->getDefinition()->addArguments($annotation->arguments);
        }

        if ($annotation->options) {
            $annotation->options = array_map(static function ($option): InputOption {
                if ($option instanceof InputOption) {
                    return $option;
                }

                if (is_array($option)) {
                    return new InputOption(...$option);
                }

                throw new LogicException(sprintf('Invalid option type: %s.', gettype($option)));
            }, $annotation->options);

            $command->getDefinition()->addOptions($annotation->options);
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
