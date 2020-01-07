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

namespace Hyperf\HttpAuth;

use Hyperf\HttpAuth\Contract\Authenticatable;
use Hyperf\HttpAuth\Contract\UserProvider;
use Hyperf\HttpAuth\Exception\AuthenticationException;

trait GuardHelpers
{
    /**
     * The currently authenticated user.
     *
     * @var \Hyperf\HttpAuth\Contract\Authenticatable
     */
    protected $user;

    /**
     * The user provider implementation.
     *
     * @var \Hyperf\HttpAuth\Contract\UserProvider
     */
    protected $provider;

    /**
     * Determine if current user is authenticated. If not, throw an exception.
     *
     * @throws \Hyperf\HttpAuth\Exception\AuthenticationException
     * @return \Hyperf\HttpAuth\Contract\Authenticatable
     */
    public function authenticate()
    {
        if (! is_null($user = $this->user())) {
            return $user;
        }

        throw new AuthenticationException();
    }

    /**
     * Determine if the guard has a user instance.
     *
     * @return bool
     */
    public function hasUser()
    {
        return ! is_null($this->user);
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check()
    {
        return ! is_null($this->user());
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        return ! $this->check();
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return null|int
     */
    public function id()
    {
        if ($this->user()) {
            return $this->user()->getAuthIdentifier();
        }
    }

    /**
     * Get the name for the currently authenticated user.
     *
     * @return null|int
     */
    public function name()
    {
        if ($this->user()) {
            return $this->user()->getUsername();
        }
    }

    /**
     * Set the current user.
     *
     * @return $this
     */
    public function setUser(Authenticatable $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the user provider used by the guard.
     *
     * @return \Hyperf\HttpAuth\Contract\UserProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * Set the user provider used by the guard.
     */
    public function setProvider(UserProvider $provider)
    {
        $this->provider = $provider;
    }
}
