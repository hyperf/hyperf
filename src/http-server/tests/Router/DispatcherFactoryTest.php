<?php


namespace HyperfTest\HttpServer\Router;

use HyperfTest\HttpServer\Stub\DispatcherFactory;
use PHPUnit\Framework\TestCase;

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