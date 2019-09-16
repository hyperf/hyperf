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

namespace HyperfTest\DistributedLock\Cases;

use Hyperf\DistributedLock\Helper\StringHelper;
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

        $string = StringHelper::format('test', ['id' => 1], '_#{id}', ':');
        $this->assertSame('test:_1', $string);

        $string = StringHelper::format('test', ['id' => 1, 'name' => 'Hyperf'], '_#{id}_#{name}', ':');
        $this->assertSame('test:_1_Hyperf', $string);

        $string = StringHelper::format('test', ['id' => 1], '#{id}', '/');
        $this->assertSame('test/1', $string);

        $string = StringHelper::format('test', ['id' => 1, 'name' => 'Hyperf'], '#{id}_#{name}', '/');
        $this->assertSame('test/1_Hyperf', $string);

        $string = StringHelper::format('test', ['id' => 1, 'name' => 'Hyperf'], '#{id}/#{name}','/');
        $this->assertSame('test/1/Hyperf', $string);
    }
}
