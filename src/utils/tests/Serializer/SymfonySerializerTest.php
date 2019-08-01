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

namespace HyperfTest\Utils\Serializer;

use Hyperf\Utils\Serializer\SerializerFactory;
use Hyperf\Utils\Serializer\SymfonyNormalizer;
use HyperfTest\Utils\Stub\Foo;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
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
    }

    public function testDenormalize()
    {
        $serializer = $this->createSerializer();
        $ret = $serializer->denormalize([[
            'int' => 10,
            'string' => null,
        ]], Foo::class . '[]');
        // var_export($ret);
        $this->assertInstanceOf(Foo::class, $ret[0]);
        $this->assertEquals(10, $ret[0]->int);
    }

    public function testException()
    {
        $serializer = $this->createSerializer();
        $e = new \InvalidArgumentException('invalid param value foo');
        $ret = $serializer->normalize($e);
        // var_export($ret);
        $obj = $serializer->denormalize($ret, \InvalidArgumentException::class);
        $this->assertInstanceOf(\InvalidArgumentException::class, $obj);
        $this->assertEquals($e->getMessage(), $obj->getMessage());
    }

    protected function createSerializer()
    {
        return new SymfonyNormalizer((new SerializerFactory())->__invoke());
    }
}
