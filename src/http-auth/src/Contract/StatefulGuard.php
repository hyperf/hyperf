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

namespace Hyperf\HttpAuth\Contract;

interface StatefulGuard extends Guard
{
    public function __construct($config, UserProvider $provider);

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param bool $remember
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = false);

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @return bool
     */
    public function once(array $credentials = []);

    /**
     * Log a user into the application.
     *
     * @param bool $remember
     */
    public function login(Authenticatable $user, $remember = false);

    /**
     * Log the given user ID into the application.
     *
     * @param mixed $id
     * @param bool $remember
     * @return \Hyperf\HttpAuth\Contract\Authenticatable
     */
    public function loginUsingId($id, $remember = false);

    /**
     * Log the given user ID into the application without sessions or cookies.
     *
     * @param mixed $id
     * @return bool
     */
    public function onceUsingId($id);

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     *
     * @return bool
     */
    public function viaRemember();

    /**
     * Log the user out of the application.
     */
    public function logout();
}
