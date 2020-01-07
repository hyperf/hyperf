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

namespace HyperfTest\Cases;

use Hyperf\HttpAuth\Config;
use Hyperf\HttpAuth\Contract\Guard;
use Hyperf\HttpAuth\Contract\UserProvider;
use Hyperf\HttpAuth\HttpAuthManage;
use HyperfTest\Demo\DemoGuard;
use HyperfTest\Demo\DemoUserModel;
use HyperfTest\Demo\DemoUserProvider;

/**
 * @internal
 * @coversNothing
 */
class HttpAuthManageTest extends AbstractTestCase
{
    public function testSetAnnotation()
    {
        $this->setAnnotations();

        $this->assertEquals(DemoGuard::class, Config::getAnnotation('test', Guard::class));
        $this->assertEquals(DemoUserProvider::class, Config::getAnnotation('test', UserProvider::class));
    }

    public function testGuard()
    {
        $guard = $this->auth()->guard();

        $this->assertEquals(true, $guard instanceof DemoGuard);
        $this->assertEquals(true, $guard->getProvider() instanceof DemoUserProvider);
    }

    public function testIdentifierUser()
    {
        $guard = $this->auth()->guard();

        $user = $this->user();

        $guard->setUser($user);

        $this->assertEquals(1, $guard->id());
        $this->assertEquals('administrator', $guard->name());
    }

    protected function setAnnotations()
    {
        Config::setAnnotation('test', DemoGuard::class, Guard::class);
        Config::setAnnotation('test', DemoUserProvider::class, UserProvider::class);
    }

    protected function config()
    {
        return new \Hyperf\Config\Config([
            'http-auth' => [
                'defaults' => [
                    'guard' => 'web',
                ],

                'guards' => [
                    'web' => [
                        'driver' => 'test', // guard provider name
                        'provider' => 'test-user-provider',
                    ],
                ],

                'providers' => [
                    'test-user-provider' => [
                        'driver' => 'test', // user provider name
                        // ... others config
                    ],
                ],
            ],
        ]);
    }

    protected function auth()
    {
        return new HttpAuthManage($this->config());
    }

    protected function user()
    {
        return new DemoUserModel();
    }
}
