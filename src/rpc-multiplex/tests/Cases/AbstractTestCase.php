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
namespace HyperfTest\RpcMultiplex\Cases;

use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Class AbstractTestCase.
 */
abstract class AbstractTestCase extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    protected function invoke($class, string $method)
    {
        $ref = new ReflectionClass($class);
        $method = $ref->getMethod($method);
        $method->setAccessible(true);
        return $method->invoke($method);
    }
}
