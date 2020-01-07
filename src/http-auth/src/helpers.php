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

use Hyperf\HttpAuth\Contract\HttpAuthContract;
use Hyperf\Utils\ApplicationContext;

if (! function_exists('auth')) {
    /**
     * @param null $guard
     * @return \Hyperf\HttpAuth\Contract\HttpAuthContract
     */
    function auth($guard = null)
    {
        $auth = ApplicationContext::getContainer()->get(HttpAuthContract::class);

        if (is_null($guard)) {
            return $auth;
        }

        return $auth->guard($guard);
    }
}
