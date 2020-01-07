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

/**
 * Interface HttpAuthContract.
 *
 * @method bool check()
 * @method bool guest()
 *
 * @see \Hyperf\HttpAuth\Contract\Guard
 * @method null|\Hyperf\HttpAuth\Contract\Authenticatable user()
 * @method null|int|string id()
 * @method null|string name()
 * @method bool validate(array $credentials = [])
 * @method setUser(\Hyperf\HttpAuth\Contract\Authenticatable $user)
 *
 * @see \Hyperf\HttpAuth\Contract\StatefulGuard
 * @method bool attempt(array $credentials = [], $remember = false)
 * @method bool once(array $credentials = [])
 * @method login(Authenticatable $user, $remember = false)
 * @method \Hyperf\HttpAuth\Contract\Authenticatable loginUsingId($id, $remember = false)
 * @method bool onceUsingId($id)
 * @method viaRemember()
 * @method logout()
 */
interface HttpAuthContract
{
    /**
     * @param null|string $name
     * @return \Hyperf\HttpAuth\Contract\Guard
     */
    public function guard($name = null): Guard;

    /**
     * @param string $name
     */
    public function shouldUse($name);
}
