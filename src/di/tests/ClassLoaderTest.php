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

use Hyperf\Di\ClassLoader;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function Hyperf\Support\env;

/**
 * @internal
 * @coversNothing
 */
class ClassLoaderTest extends TestCase
{
    public function testDotEnv()
    {
        $class = new class() extends ClassLoader {
            public function __construct()
            {
            }
        };

        $ref = new ReflectionClass($class);
        $method = $ref->getMethod('loadDotenv');
        $method->setAccessible(true);
        $method->invoke($class);

        $this->assertNotEquals('0.0.0', env('SW_VERSION'));
        $this->assertSame('Hyperf', env('PHP_FRAMEWORK'));
    }
}
