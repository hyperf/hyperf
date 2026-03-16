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

/**
 * @method static string getMessage(int|string $code, array $translate = null)
 */
abstract class AbstractConstants
{
    use ConstantsTrait;
}
