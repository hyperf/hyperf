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

namespace Hyperf\Constants;

use Hyperf\Constants\Exception\ConstantsException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @method static string getMessage(int|string $code, array $translate = null)
 */
trait ConstantsTrait
{
    use GetterTrait;

    /**
     * @throws ConstantsException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function __callStatic(string $name, array $arguments): array|string
    {
        return static::getValue($name, $arguments);
    }
}
