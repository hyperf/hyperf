<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE.md
 */

namespace HyperfTest\HttpMessage;

use Hyperf\HttpMessage\Base\Request;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
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
        ]);

        $this->assertSame($token, $request->getHeaderLine('X-Token'));
        $this->assertSame((string) $id, $request->getHeaderLine('X-Id'));
        $this->assertSame('1', $request->getHeaderLine('Version'));
        $this->assertSame('1000', $request->getHeaderLine('1000'));
    }
}
