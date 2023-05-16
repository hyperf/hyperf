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

use Hyperf\Serializer\ExceptionNormalizer;
use HyperfTest\Serializer\Stub\FooException;
use HyperfTest\Serializer\Stub\SerializableException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ExceptionNormalizerTest extends TestCase
{
    public function testInvalidArgumentException()
    {
        $normalizer = new ExceptionNormalizer();
        $ex = new InvalidArgumentException('invalid param foo');
        $result = $normalizer->normalize($ex);
        $ret = $normalizer->denormalize($result, InvalidArgumentException::class);
        $this->assertInstanceOf(InvalidArgumentException::class, $ret);
        $this->assertEquals($ret->getMessage(), $ex->getMessage());
        $this->assertEquals($ret, $ex);
    }

    public function testSerializableException()
    {
        $normalizer = new ExceptionNormalizer();
        $ex = new SerializableException('invalid param foo');
        $result = $normalizer->normalize($ex);
        $ret = $normalizer->denormalize($result, SerializableException::class);
        $this->assertInstanceOf(SerializableException::class, $ret);
        $this->assertEquals($ret->getMessage(), $ex->getMessage());
        $this->assertEquals($ret, $ex);

        $ex = new FooException(1000, 'invalid param foo');
        $result = $normalizer->normalize($ex);
        $ret = $normalizer->denormalize($result, FooException::class);
        $this->assertInstanceOf(FooException::class, $ret);
        $this->assertEquals($ret->getMessage(), $ex->getMessage());
        $this->assertEquals($ret, $ex);
    }
}
