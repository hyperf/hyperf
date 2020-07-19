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
namespace Hyperf\Validation\Contract;

/**
 * Keep the same interface name as illuminate/validation, but actually this method does not
 * called after object resolved, this method will call in Hyperf\Validation\Middleware\ValidationMiddleware.
 */
interface ValidatesWhenResolved
{
    /**
     * Validate the given class instance.
     */
    public function validateResolved();
}
