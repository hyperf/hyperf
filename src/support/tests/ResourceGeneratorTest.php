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

namespace HyperfTest\Support;

use Hyperf\Support\ResourceGenerator;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ResourceGeneratorTest extends TestCase
{
    public function testFrom()
    {
        $data = '123123';
        $resource = ResourceGenerator::from($data);
        $this->assertSame('1', fread($resource, 1));
        $this->assertSame('23', fread($resource, 2));
        $this->assertSame('123', fread($resource, 10));
    }

    public function testFromMemoryLeak()
    {
        $data = str_repeat('1', 1024 * 1024);
        $memory = memory_get_usage(true);
        for ($i = 0; $i < 100; ++$i) {
            ResourceGenerator::fromMemory($data);
            $current = memory_get_usage(true);
            $leak = $current - $memory;
            $memory = $current;
        }

        $this->assertSame(0, $leak);
    }
}
