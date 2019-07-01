<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\HttpServer\Router;

use HyperfTest\HttpServer\Stub\DispatcherFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class DispatcherFactoryTest extends TestCase
{
    public function testGetPrefix()
    {
        $factory = new DispatcherFactory();

        $res = $factory->getPrefix('App\\Controller\\Admin\\UserController', '');
        $this->assertSame('/admin/user', $res);

        $res = $factory->getPrefix('App\\Controller\\Admin\\UserAuthController', '');
        $this->assertSame('/admin/user_auth', $res);
    }
}
