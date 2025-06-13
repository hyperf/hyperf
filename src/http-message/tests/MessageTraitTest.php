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

use Hyperf\HttpMessage\Base\Request;
use Hyperf\HttpMessage\Server\Request as ServerRequest;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class MessageTraitTest extends TestCase
{
    public function testSetHeaders()
    {
        $token = uniqid();
        $id = rand(1000, 9999);
        $request = new Request('GET', '/', [
            'X-Token' => $token,
            'X-Id' => $id,
            'Version' => 1.0,
            1000 => 1000,
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $this->assertSame($token, $request->getHeaderLine('X-Token'));
        $this->assertSame((string) $id, $request->getHeaderLine('X-Id'));
        $this->assertSame('1', $request->getHeaderLine('Version'));
        $this->assertSame('1000', $request->getHeaderLine('1000'));
        $this->assertSame('XMLHttpRequest', $request->getHeaderLine('X-Requested-With'));
    }

    public function testIsXmlHttpRequest()
    {
        $request = new ServerRequest('GET', '/', [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $this->assertTrue($request->isXmlHttpRequest());
    }
}
