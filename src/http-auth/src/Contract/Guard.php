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

interface Guard
{
    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check();

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest();

    /**
     * Get the currently authenticated user.
     *
     * @return null|\Hyperf\HttpAuth\Contract\Authenticatable
     */
    public function user();

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return null|int|string
     */
    public function id();

    /**
     * Get the name for the currently authenticated user.
     *
     * @return null|string
     */
    public function name();

    /**
     * Validate a user's credentials.
     *
     * @return bool
     */
    public function validate(array $credentials = []);

    /**
     * Set the current user.
     *
     * @param \Hyperf\HttpAuth\Contract\Authenticatable $user
     */
    public function setUser(Authenticatable $user);
}
