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

namespace HyperfTest\Serializer;

use Hyperf\Serializer\SerializerFactory;
use Hyperf\Serializer\SymfonyNormalizer;
use HyperfTest\Serializer\Stub\Foo;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class SymfonySerializerTest extends TestCase
{
    public function testNormalize()
    {
        $serializer = $this->createSerializer();
        $object = new Foo();
        $object->int = 10;
        $ret = $serializer->normalize([$object]);
        $this->assertEquals([[
            'int' => 10,
            'string' => null,
        ]], $ret);

        $ret = $serializer->normalize([1, '2']);
        $this->assertEquals([1, '2'], $ret);
    }

    public function testDenormalize()
    {
        $serializer = $this->createSerializer();
        $ret = $serializer->denormalize([[
            'int' => 10,
            'string' => null,
        ]], Foo::class . '[]');
        $this->assertInstanceOf(Foo::class, $ret[0]);
        $this->assertEquals(10, $ret[0]->int);

        $ret = $serializer->denormalize('1', 'mixed');
        $this->assertSame('1', $ret);

        $ret = $serializer->denormalize(['1', 2, '03'], 'mixed[]');
        $this->assertSame(['1', 2, '03'], $ret);

        $serializer = $this->createSerializer();
        $ret = $serializer->denormalize('1', 'int');
        $this->assertSame(1, $ret);

        $ret = $serializer->denormalize(['1', 2, '03'], 'int[]');
        $this->assertSame([1, 2, 3], $ret);
    }

    public function testDenormalizeWithWrongType()
    {
        $this->markTestSkipped('The test cases skipped, when `symfony/serializer` >= v5.1.9');

        $this->expectException(NotNormalizableValueException::class);
        $this->expectExceptionMessageMatches('/Data expected to be of type/');

        $serializer = $this->createSerializer();
        $ret = $serializer->denormalize('1', 'int');
        $this->assertSame(1, $ret);

        $ret = $serializer->denormalize(['1', 2, '03'], 'int[]');
        $this->assertSame([1, 2, 3], $ret);
    }

    public function testException()
    {
        $serializer = $this->createSerializer();
        $e = new InvalidArgumentException('invalid param value foo');
        $ret = $serializer->normalize($e);
        $obj = $serializer->denormalize($ret, InvalidArgumentException::class);
        $this->assertInstanceOf(InvalidArgumentException::class, $obj);
        $this->assertEquals($e->getMessage(), $obj->getMessage());
    }

    protected function createSerializer()
    {
        return new SymfonyNormalizer((new SerializerFactory())->__invoke());
    }
}
