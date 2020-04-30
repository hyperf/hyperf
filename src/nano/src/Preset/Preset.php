<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Nano\Preset;

/**
 * The preset class represents a set of configs.
 * All includes path should be static, ie. no variables.
 * This is because Phar and static analyzer doesn't work
 * well with dynamic includes.
 */
class Preset
{
    public static function default(): array
    {
        return include __DIR__ . '/Default.php';
    }

    public static function base(): array
    {
        return include __DIR__ . '/Base.php';
    }
}
