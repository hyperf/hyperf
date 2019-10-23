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

namespace HyperfTest\Cache\Cases;

use Hyperf\Cache\Helper\StringHelper;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class StringHelperTest extends TestCase
{
    public function testFormat()
    {
        $string = StringHelper::format('test', ['id' => 1], '_#{id}');
        $this->assertSame('test:_1', $string);

        $string = StringHelper::format('test', ['id' => 1, 'name' => 'Hyperf'], '_#{id}');
        $this->assertSame('test:_1', $string);

        $string = StringHelper::format('test', ['id' => 1, 'name' => 'Hyperf']);
        $this->assertSame('test:1:Hyperf', $string);
    }
}
