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

namespace Hyperf\Command;

use Closure;

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
}
