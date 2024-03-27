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

namespace Hyperf\Crontab;

use Closure;

use function Hyperf\Tappable\tap;

class Schedule
{
    public const ROUTE = BASE_PATH . '/config/crontabs.php';

    /**
     * @var Crontab[]
     */
    public static array $crontabs = [];

    public static function load(): void
    {
        if (is_file(self::ROUTE)) {
            require_once self::ROUTE;
        }
    }

    public static function command(string $command, array $arguments = []): Crontab
    {
        $arguments = array_merge(['command' => $command], $arguments);

        if (! isset($arguments['--disable-event-dispatcher'])) {
            $arguments['--disable-event-dispatcher'] = true;
        }

        return tap(new Crontab(), fn ($crontab) => self::$crontabs[] = $crontab)
            ->setType('command')
            ->setCallback($arguments);
    }

    public static function call(mixed $callable): Crontab
    {
        $type = $callable instanceof Closure ? 'closure' : 'callback';

        return tap(new Crontab(), fn ($crontab) => self::$crontabs[] = $crontab)
            ->setType($type)
            ->setCallback($callable);
    }

    /**
     * @return Crontab[]
     */
    public static function getCrontabs(): array
    {
        return self::$crontabs;
    }
}
