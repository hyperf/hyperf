<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\HttpMessage;

use Hyperf\HttpMessage\Cookie\SetCookie;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class CookieTest extends TestCase
{
    public function testSetCookeFromString()
    {
        $cookie = SetCookie::fromString('ltoken_v2=v2_RaLe3_kws2BrlGKF2aqhcCWH7PEybgGpmdcZXcOmEuu3sEgoA==.CAE=; Path=/; Domain=miyoushe.com; Max-Age=31536000; HttpOnly; Secure');
        $this->assertSame(31536000, $cookie->getMaxAge());
        $this->assertIsInt($cookie->getExpires());
        $this->assertTrue($cookie->getSecure());
    }
}
