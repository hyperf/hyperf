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
namespace Hyperf\Utils\Traits;

/**
 * @deprecated since 3.1, please use `\Hyperf\Tappable\Tappable` instead.
 */
trait Tappable
{
    /**
     * Call the given Closure with this instance then return the instance.
     *
     * @return mixed
     */
    public function tap(?callable $callback = null)
    {
        return tap($this, $callback);
    }
}
