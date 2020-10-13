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
namespace HyperfTest\Utils;

use Hyperf\Utils\Resource;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ResourceTest extends TestCase
{
    public function testFrom()
    {
        $data = '123123';
        $resource = Resource::from($data);
        $this->assertSame('1', fread($resource, 1));
        $this->assertSame('23', fread($resource, 2));
        $this->assertSame('123', fread($resource, 10));
    }

    public function testFromString()
    {
        $strings = [
            '123',
            str_repeat('1', 1024 * 1024),
            str_repeat('1', 1024 * 1024 * 100),
        ];

        foreach ($strings as $data) {
            $t = microtime(true);
            $resource = Resource::from($data, $filename = 'php://temp');
            $time1 = microtime(true) - $t;
            $data1 = fread($resource, 1024 * 1024 * 100);

            $t = microtime(true);
            $resource = Resource::from($data, $filename = 'php://memory');
            $time2 = microtime(true) - $t;
            $data2 = fread($resource, 1024 * 1024 * 100);

            $t = microtime(true);
            $filename = BASE_PATH . '/runtime/' . uniqid();
            if (! file_exists($filename)) {
                file_put_contents($filename, '');
            }
            $resource = Resource::from($data, $filename);
            $time3 = microtime(true) - $t;
            $data3 = fread($resource, 1024 * 1024 * 100);

            $this->assertSame($data, $data1);
            $this->assertSame($data, $data2);
            $this->assertSame($data2, $data3);
            $this->assertLessThan($time1, $time2);
            $this->assertLessThan($time3, $time1);
        }
    }

    public function testFromMemoryLeak()
    {
        $data = str_repeat('1', 1024 * 1024);
        $memory = memory_get_usage(true);
        for ($i = 0; $i < 100; ++$i) {
            $resource = Resource::from($data, $filename = 'php://memory');
            $current = memory_get_usage(true);
            $leak = $current - $memory;
            $memory = $current;
        }

        $this->assertSame(0, $leak);
    }
}
