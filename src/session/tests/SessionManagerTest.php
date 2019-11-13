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

namespace HyperfTest\Session;

use Hyperf\Contract\ConfigInterface;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\Session\Session;
use Hyperf\Session\SessionManager;
use Hyperf\Utils\Str;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;

/**
 * @internal
 * @covers \Hyperf\Session\SessionManager
 */
class SessionManagerTest extends TestCase
{
    public function testSetterAndGetter()
    {
        $sessionManager = new SessionManager(Mockery::mock(ContainerInterface::class), Mockery::mock(ConfigInterface::class));
        $sessionManager = $sessionManager->setSession($mockSession = Mockery::mock(Session::class));
        $this->assertInstanceOf(SessionManager::class, $sessionManager);

        $session = $sessionManager->getSession();
        $this->assertInstanceOf(Session::class, $session);
        $this->assertSame($mockSession, $session);
    }

    public function testParseSessionId()
    {
        $request = new Request('get', '/');
        $sessionManager = new SessionManager(Mockery::mock(ContainerInterface::class), Mockery::mock(ConfigInterface::class));
        $reflectionClass = new ReflectionClass(SessionManager::class);
        $parseSessionIdMethod = $reflectionClass->getMethod('parseSessionId');
        $parseSessionIdMethod->setAccessible(true);
        $id = Str::random(40);
        $this->assertSame($id, $parseSessionIdMethod->invoke($sessionManager, $request->withCookieParams([
            'HYPERF_SESSION_ID' => $id,
        ])));
        $this->assertSame('123', $parseSessionIdMethod->invoke($sessionManager, $request->withCookieParams([
            'HYPERF_SESSION_ID' => 123,
        ])));
        $this->assertNull($parseSessionIdMethod->invoke($sessionManager, $request->withCookieParams([
            'foo' => 'bar',
        ])));
    }
}
