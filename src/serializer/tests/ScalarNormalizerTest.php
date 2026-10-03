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

use Hyperf\Serializer\ScalarNormalizer;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class ScalarNormalizerTest extends TestCase
{
    private ScalarNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new ScalarNormalizer();
    }

    #[DataProvider('denormalizeProvider')]
    public function testDenormalize($data, string $type, $expected)
    {
        $result = $this->normalizer->denormalize($data, $type);
        $this->assertSame($expected, $result);
    }

    public static function denormalizeProvider(): array
    {
        return [
            // int type
            ['123', 'int', 123],
            [123.45, 'int', 123],
            ['0', 'int', 0],
            [true, 'int', 1],
            [false, 'int', 0],

            // string type
            [123, 'string', '123'],
            [123.45, 'string', '123.45'],
            [true, 'string', '1'],
            [false, 'string', ''],
            ['hello', 'string', 'hello'],

            // float type
            ['123.45', 'float', 123.45],
            [123, 'float', 123.0],
            ['0.0', 'float', 0.0],
            [true, 'float', 1.0],
            [false, 'float', 0.0],

            // bool type
            [1, 'bool', true],
            [0, 'bool', false],
            ['true', 'bool', true],
            ['', 'bool', false],
            [123, 'bool', true],

            // default (unknown type) - returns data as-is
            ['test', 'unknown', 'test'],
            [123, 'SomeClass', 123],
        ];
    }

    #[DataProvider('supportsDenormalizationProvider')]
    public function testSupportsDenormalization(string $type, bool $expected)
    {
        $result = $this->normalizer->supportsDenormalization(null, $type);
        $this->assertSame($expected, $result);
    }

    public static function supportsDenormalizationProvider(): array
    {
        return [
            ['int', true],
            ['string', true],
            ['float', true],
            ['bool', true],
            ['mixed', true],
            ['array', true],
            ['object', false],
            ['stdClass', false],
            ['DateTime', false],
            ['SomeCustomClass', false],
        ];
    }

    #[DataProvider('normalizeProvider')]
    public function testNormalize($data)
    {
        $result = $this->normalizer->normalize($data);
        $this->assertSame($data, $result);
    }

    public static function normalizeProvider(): array
    {
        return [
            [123],
            ['string'],
            [123.45],
            [true],
            [false],
            [null],
        ];
    }

    #[DataProvider('supportsNormalizationProvider')]
    public function testSupportsNormalization($data, bool $expected)
    {
        $result = $this->normalizer->supportsNormalization($data);
        $this->assertSame($expected, $result);
    }

    public static function supportsNormalizationProvider(): array
    {
        return [
            [123, true],
            ['string', true],
            [123.45, true],
            [true, true],
            [false, true],
            [null, false],
            [[], false],
            [new stdClass(), false],
            [[1, 2, 3], false],
        ];
    }

    public function testGetSupportedTypes()
    {
        $result = $this->normalizer->getSupportedTypes(null);
        $this->assertSame(['*' => true], $result);
    }

    public function testGetSupportedTypesWithFormat()
    {
        $result = $this->normalizer->getSupportedTypes('json');
        $this->assertSame(['*' => true], $result);
    }

    public function testGetSupportedTypesReturnsFalseForSubclass()
    {
        $subclass = new class extends ScalarNormalizer {
        };
        $result = $subclass->getSupportedTypes(null);
        $this->assertSame(['*' => false], $result);
    }

    public function testDenormalizeWithContext()
    {
        $result = $this->normalizer->denormalize('123', 'int', null, ['some' => 'context']);
        $this->assertSame(123, $result);
    }

    public function testNormalizeWithContext()
    {
        $result = $this->normalizer->normalize(123, null, ['some' => 'context']);
        $this->assertSame(123, $result);
    }

    public function testSupportsDenormalizationWithContext()
    {
        $result = $this->normalizer->supportsDenormalization(null, 'int', null, ['some' => 'context']);
        $this->assertTrue($result);
    }

    public function testSupportsNormalizationWithContext()
    {
        $result = $this->normalizer->supportsNormalization(123, null, ['some' => 'context']);
        $this->assertTrue($result);
    }
}
