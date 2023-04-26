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
namespace Hyperf\Utils\Exception;

class_alias(\Hyperf\Support\Exception\ExceptionThrower::class, ExceptionThrower::class);

if (! class_exists(ExceptionThrower::class)) {
    /**
     * @deprecated since 3.1, use \Hyperf\Support\Exception\ExceptionThrower instead.
     * @phpstan-ignore-next-line
     */
    final class ExceptionThrower extends \Hyperf\Support\Exception\ExceptionThrower
    {
    }
}
