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

namespace HyperfTest\Testing;

use Hyperf\Testing\Debug;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class DebugTest extends TestCase
{
    public function testGetRefCount()
    {
        $this->assertSame('1', Debug::getRefCount(new stdClass()));
        $obj = new stdClass();
        $this->assertSame('2', Debug::getRefCount($obj));
        $obj2 = new stdClass();
        $obj2->obj = $obj;
        $this->assertSame('2', Debug::getRefCount($obj2));
        $this->assertSame('3', Debug::getRefCount($obj));
        $fun = static function () {};
        $this->assertSame('2', Debug::getRefCount($fun));
        $this->assertSame('1', Debug::getRefCount(function () {}));
    }
}
