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

namespace HyperfTest\Di;

use Hyperf\Di\Annotation\ScanConfig;
use Hyperf\Di\Annotation\Scanner;
use Hyperf\Di\Exception\DirectoryNotExistException;
use Hyperf\Di\ScanHandler\NullScanHandler;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
/**
 * @internal
 * @coversNothing
 */
class AnnotationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testScanAnnotationsDirectoryNotExist()
    {
        $scanner = new Scanner(new ScanConfig(false, '/'), new NullScanHandler());
        $ref = new ReflectionClass($scanner);
        $method = $ref->getMethod('normalizeDir');

        $this->expectException(DirectoryNotExistException::class);
        $method->invokeArgs($scanner, [['/not_exists']]);
    }

    public function testScanAnnotationsDirectoryEmpty()
    {
        $scanner = new Scanner(new ScanConfig(false, '/'), new NullScanHandler());
        $ref = new ReflectionClass($scanner);
        $method = $ref->getMethod('normalizeDir');

        $this->assertSame([], $method->invokeArgs($scanner, [[]]));
    }

    public function testVariadicParams()
    {
        $foo = new FooParams(id: 1, name: 'Hyperf');

        $this->assertSame(['id' => 1, 'name' => 'Hyperf'], $foo->param);
    }
}

class FooParams
{
    public array $param;

    public function __construct(...$params)
    {
        $this->param = $params;
    }
}
