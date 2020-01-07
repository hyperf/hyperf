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

namespace HyperfTest\Demo;

use Hyperf\HttpAuth\Annotation\UserProviderAnnotation;
use Hyperf\HttpAuth\Contract\Authenticatable;
use Hyperf\HttpAuth\Contract\UserProvider;

/**
 * Class TestUserProvider.
 *
 * @UserProviderAnnotation("test")
 */
class DemoUserProvider implements UserProvider
{
    public $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function retrieveById($identifier)
    {
        return null;
    }

    public function retrieveByToken($identifier, $token)
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
    }

    public function retrieveByCredentials(array $credentials)
    {
        return null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return true;
    }
}
