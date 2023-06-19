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
namespace Hyperf\ClosureCommand;

use Closure;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ApplicationInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

use function Hyperf\Support\make;
use function Hyperf\Tappable\tap;

class Console
{
    public const ROUTE = BASE_PATH . '/config/console.php';

    /**
     * @var ClosureCommand[]
     */
    protected static $commands = [];

    public static function command(string $signature, Closure $command): ClosureCommand
    {
        return tap(make(ClosureCommand::class, [
            'signature' => $signature,
            'closure' => $command,
        ]), static function ($handler) {
            $handlerId = spl_object_hash($handler);
            self::$commands[$handlerId] = $handler;
        });
    }

    /**
     * @return ClosureCommand[]
     */
    public static function getCommands(): array
    {
        return self::$commands;
    }

    public static function call(string $command, array $arguments = []): int
    {
        $arguments['command'] = $command;

        $input = new ArrayInput($arguments);
        $output = new NullOutput();

        /** @var ContainerInterface $container */
        $container = ApplicationContext::getContainer();

        /** @var Application $application */
        $application = $container->get(ApplicationInterface::class);
        $application->setAutoExit(false);

        return $application->run($input, $output);
    }
}
