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
namespace HyperfTest\CodeParser;

use Hyperf\CodeParser\PhpDocReader;
use HyperfTest\Utils\Stub\DocFoo;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 * @coversNothing
 */
class PhpDocReaderTest extends TestCase
{
    public function testGetReturnClass()
    {
        $reader = PhpDocReader::getInstance();
        $ref = new ReflectionClass(DocFoo::class);
        $res = $reader->getReturnType($ref->getMethod('getBuiltinInt'));
        $this->assertSame(['?int'], $res);
        $res = $reader->getReturnType($ref->getMethod('getString'));
        $this->assertSame(['string'], $res);
        $res = $reader->getReturnType($ref->getMethod('getStringOrInt'));
        $this->assertSame(['int', 'string'], $res);
        $res = $reader->getReturnType($ref->getMethod('getSelf'), true);
        $this->assertSame(['DocFoo'], $res);
        $res = $reader->getReturnType($ref->getMethod('getSelf'));
        $this->assertSame([DocFoo::class], $res);
        $res = $reader->getReturnType($ref->getMethod('getSelfOrNot'));
        $this->assertSame(['bool', DocFoo::class], $res);
        $res = $reader->getReturnType($ref->getMethod('getSelfOrNot'), true);
        $this->assertSame(['bool', 'DocFoo'], $res);
    }
}
