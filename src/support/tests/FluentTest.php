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

use ArrayIterator;
use Carbon\Carbon;
use Hyperf\Collection\Collection;
use Hyperf\Stringable\Stringable;
use Hyperf\Support\Fluent;
use HyperfTest\Support\Stub\TestBackedEnum;
use HyperfTest\Support\Stub\TestEnum;
use HyperfTest\Support\Stub\TestStringBackedEnum;
use InvalidArgumentException;
use IteratorAggregate;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use stdClass;

use function Hyperf\Collection\collect;

include_once 'Stub/Enums.php';

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class FluentTest extends TestCase
{
    public function testAttributesAreSetByConstructor()
    {
        $array = ['name' => 'Taylor', 'age' => 25];
        $fluent = new Fluent($array);

        $refl = new ReflectionObject($fluent);
        $attributes = $refl->getProperty('attributes');

        $this->assertEquals($array, $attributes->getValue($fluent));
        $this->assertEquals($array, $fluent->getAttributes());
    }

    public function testAttributesAreSetByConstructorGivenstdClass()
    {
        $array = ['name' => 'Taylor', 'age' => 25];
        $fluent = new Fluent((object) $array);

        $refl = new ReflectionObject($fluent);
        $attributes = $refl->getProperty('attributes');

        $this->assertEquals($array, $attributes->getValue($fluent));
        $this->assertEquals($array, $fluent->getAttributes());
    }

    public function testAttributesAreSetByConstructorGivenArrayIterator()
    {
        $array = ['name' => 'Taylor', 'age' => 25];
        $fluent = new Fluent(new FluentArrayIteratorStub($array));

        $refl = new ReflectionObject($fluent);
        $attributes = $refl->getProperty('attributes');

        $this->assertEquals($array, $attributes->getValue($fluent));
        $this->assertEquals($array, $fluent->getAttributes());
    }

    public function testGetMethodReturnsAttribute()
    {
        $fluent = new Fluent(['name' => 'Taylor']);

        $this->assertSame('Taylor', $fluent->get('name'));
        $this->assertSame('Default', $fluent->get('foo', 'Default'));
        $this->assertSame('Taylor', $fluent->name);
        $this->assertNull($fluent->foo);
    }

    public function testSetMethodSetsAttribute()
    {
        $fluent = new Fluent();

        $fluent->set('name', 'Taylor');
        $fluent->set('developer', true);
        $fluent->set('posts', 25);
        $fluent->set('computer.color', 'silver');

        $this->assertSame('Taylor', $fluent->name);
        $this->assertTrue($fluent->developer);
        $this->assertSame(25, $fluent->posts);
        $this->assertSame(['color' => 'silver'], $fluent->computer);
    }

    public function testArrayAccessToAttributes()
    {
        $fluent = new Fluent(['attributes' => '1']);

        $this->assertTrue(isset($fluent['attributes']));
        $this->assertEquals(1, $fluent['attributes']);

        $fluent->attributes();

        $this->assertTrue($fluent['attributes']);
    }

    public function testMagicMethodsCanBeUsedToSetAttributes()
    {
        $fluent = new Fluent();

        $fluent->name = 'Taylor';
        $fluent->developer();
        $fluent->age(25);

        $this->assertSame('Taylor', $fluent->name);
        $this->assertTrue($fluent->developer);
        $this->assertEquals(25, $fluent->age);
        $this->assertInstanceOf(Fluent::class, $fluent->programmer());
    }

    public function testIssetMagicMethod()
    {
        $array = ['name' => 'Taylor', 'age' => 25];
        $fluent = new Fluent($array);

        $this->assertTrue(isset($fluent->name));

        unset($fluent->name);

        $this->assertFalse(isset($fluent->name));
    }

    public function testToArrayReturnsAttribute()
    {
        $array = ['name' => 'Taylor', 'age' => 25];
        $fluent = new Fluent($array);

        $this->assertEquals($array, $fluent->toArray());
    }

    public function testToJsonEncodesTheToArrayResult()
    {
        $fluent = $this->getMockBuilder(Fluent::class)->onlyMethods(['toArray'])->getMock();
        $fluent->expects($this->once())->method('toArray')->willReturn(['foo']);
        $results = $fluent->toJson();

        $this->assertJsonStringEqualsJsonString(json_encode(['foo']), $results);
    }

    public function testScope()
    {
        $fluent = new Fluent(['user' => ['name' => 'hyperf']]);
        $this->assertEquals(['hyperf'], $fluent->scope('user.name')->toArray());
        $this->assertEquals(['dayle'], $fluent->scope('user.age', 'dayle')->toArray());

        $fluent = new Fluent(['products' => ['forge', 'vapour', 'spark']]);
        $this->assertEquals(['forge', 'vapour', 'spark'], $fluent->scope('products')->toArray());
        $this->assertEquals(['foo', 'bar'], $fluent->scope('missing', ['foo', 'bar'])->toArray());

        $fluent = new Fluent(['authors' => ['hyperf' => ['products' => ['forge', 'vapour', 'spark']]]]);
        $this->assertEquals(['forge', 'vapour', 'spark'], $fluent->scope('authors.hyperf.products')->toArray());
    }

    public function testToCollection()
    {
        $fluent = new Fluent(['forge', 'vapour', 'spark']);
        $this->assertEquals(['forge', 'vapour', 'spark'], $fluent->collect()->all());

        $fluent = new Fluent(['authors' => ['taylor' => ['products' => ['forge', 'vapour', 'spark']]]]);
        $this->assertEquals(['forge', 'vapour', 'spark'], $fluent->collect('authors.taylor.products')->all());
    }

    public function testStringMethod()
    {
        $fluent = new Fluent([
            'int' => 123,
            'int_str' => '456',
            'float' => 123.456,
            'float_str' => '123.456',
            'float_zero' => 0.000,
            'float_str_zero' => '0.000',
            'str' => 'abc',
            'empty_str' => '',
            'null' => null,
        ]);
        $this->assertTrue($fluent->string('int') instanceof Stringable);
        $this->assertTrue($fluent->string('unknown_key') instanceof Stringable);
        $this->assertSame('123', $fluent->string('int')->value());
        $this->assertSame('456', $fluent->string('int_str')->value());
        $this->assertSame('123.456', $fluent->string('float')->value());
        $this->assertSame('123.456', $fluent->string('float_str')->value());
        $this->assertSame('0', $fluent->string('float_zero')->value());
        $this->assertSame('0.000', $fluent->string('float_str_zero')->value());
        $this->assertSame('', $fluent->string('empty_str')->value());
        $this->assertSame('', $fluent->string('null')->value());
        $this->assertSame('', $fluent->string('unknown_key')->value());
    }

    public function testBooleanMethod()
    {
        $fluent = new Fluent(['with_trashed' => 'false', 'download' => true, 'checked' => 1, 'unchecked' => '0', 'with_on' => 'on', 'with_yes' => 'yes']);
        $this->assertTrue($fluent->boolean('checked'));
        $this->assertTrue($fluent->boolean('download'));
        $this->assertFalse($fluent->boolean('unchecked'));
        $this->assertFalse($fluent->boolean('with_trashed'));
        $this->assertFalse($fluent->boolean('some_undefined_key'));
        $this->assertTrue($fluent->boolean('with_on'));
        $this->assertTrue($fluent->boolean('with_yes'));
    }

    public function testIntegerMethod()
    {
        $fluent = new Fluent([
            'int' => '123',
            'raw_int' => 456,
            'zero_padded' => '078',
            'space_padded' => ' 901',
            'nan' => 'nan',
            'mixed' => '1ab',
            'underscore_notation' => '2_000',
            'null' => null,
        ]);
        $this->assertSame(123, $fluent->integer('int'));
        $this->assertSame(456, $fluent->integer('raw_int'));
        $this->assertSame(78, $fluent->integer('zero_padded'));
        $this->assertSame(901, $fluent->integer('space_padded'));
        $this->assertSame(0, $fluent->integer('nan'));
        $this->assertSame(1, $fluent->integer('mixed'));
        $this->assertSame(2, $fluent->integer('underscore_notation'));
        $this->assertSame(123456, $fluent->integer('unknown_key', 123456));
        $this->assertSame(0, $fluent->integer('null'));
        $this->assertSame(0, $fluent->integer('null', 123456));
    }

    public function testFloatMethod()
    {
        $fluent = new Fluent([
            'float' => '1.23',
            'raw_float' => 45.6,
            'decimal_only' => '.6',
            'zero_padded' => '0.78',
            'space_padded' => ' 90.1',
            'nan' => 'nan',
            'mixed' => '1.ab',
            'scientific_notation' => '1e3',
            'null' => null,
        ]);
        $this->assertSame(1.23, $fluent->float('float'));
        $this->assertSame(45.6, $fluent->float('raw_float'));
        $this->assertSame(.6, $fluent->float('decimal_only'));
        $this->assertSame(0.78, $fluent->float('zero_padded'));
        $this->assertSame(90.1, $fluent->float('space_padded'));
        $this->assertSame(0.0, $fluent->float('nan'));
        $this->assertSame(1.0, $fluent->float('mixed'));
        $this->assertSame(1e3, $fluent->float('scientific_notation'));
        $this->assertSame(123.456, $fluent->float('unknown_key', 123.456));
        $this->assertSame(0.0, $fluent->float('null'));
        $this->assertSame(0.0, $fluent->float('null', 123.456));
    }

    public function testArrayMethod()
    {
        $fluent = new Fluent(['users' => [1, 2, 3]]);

        $this->assertIsArray($fluent->array('users'));
        $this->assertEquals([1, 2, 3], $fluent->array('users'));
        $this->assertEquals(['users' => [1, 2, 3]], $fluent->array());

        $fluent = new Fluent(['text-payload']);
        $this->assertEquals(['text-payload'], $fluent->array());

        $fluent = new Fluent(['email' => 'test@example.com']);
        $this->assertEquals(['test@example.com'], $fluent->array('email'));

        $fluent = new Fluent([]);
        $this->assertIsArray($fluent->array());
        $this->assertEmpty($fluent->array());

        $fluent = new Fluent(['users' => [1, 2, 3], 'roles' => [4, 5, 6], 'foo' => ['bar', 'baz'], 'email' => 'test@example.com']);
        $this->assertEmpty($fluent->array(['developers']));
        $this->assertNotEmpty($fluent->array(['roles']));
        $this->assertEquals(['roles' => [4, 5, 6]], $fluent->array(['roles']));
        $this->assertEquals(['users' => [1, 2, 3], 'email' => 'test@example.com'], $fluent->array(['users', 'email']));
        $this->assertEquals(['roles' => [4, 5, 6], 'foo' => ['bar', 'baz']], $fluent->array(['roles', 'foo']));
        $this->assertEquals(['users' => [1, 2, 3], 'roles' => [4, 5, 6], 'foo' => ['bar', 'baz'], 'email' => 'test@example.com'], $fluent->array());
    }

    public function testCollectMethod()
    {
        $fluent = new Fluent(['users' => [1, 2, 3]]);

        $this->assertInstanceOf(Collection::class, $fluent->collect('users'));
        $this->assertTrue($fluent->collect('developers')->isEmpty());
        $this->assertEquals([1, 2, 3], $fluent->collect('users')->all());
        $this->assertEquals(['users' => [1, 2, 3]], $fluent->collect()->all());

        $fluent = new Fluent(['text-payload']);
        $this->assertEquals(['text-payload'], $fluent->collect()->all());

        $fluent = new Fluent(['email' => 'test@example.com']);
        $this->assertEquals(['test@example.com'], $fluent->collect('email')->all());

        $fluent = new Fluent([]);
        $this->assertInstanceOf(Collection::class, $fluent->collect());
        $this->assertTrue($fluent->collect()->isEmpty());

        $fluent = new Fluent(['users' => [1, 2, 3], 'roles' => [4, 5, 6], 'foo' => ['bar', 'baz'], 'email' => 'test@example.com']);
        $this->assertInstanceOf(Collection::class, $fluent->collect(['users']));
        $this->assertTrue($fluent->collect(['developers'])->isEmpty());
        $this->assertTrue($fluent->collect(['roles'])->isNotEmpty());
        $this->assertEquals(['roles' => [4, 5, 6]], $fluent->collect(['roles'])->all());
        $this->assertEquals(['users' => [1, 2, 3], 'email' => 'test@example.com'], $fluent->collect(['users', 'email'])->all());
        $this->assertEquals(collect(['roles' => [4, 5, 6], 'foo' => ['bar', 'baz']]), $fluent->collect(['roles', 'foo']));
        $this->assertEquals(['users' => [1, 2, 3], 'roles' => [4, 5, 6], 'foo' => ['bar', 'baz'], 'email' => 'test@example.com'], $fluent->collect()->all());
    }

    public function testDateMethod()
    {
        $fluent = new Fluent([
            'as_null' => null,
            'as_invalid' => 'invalid',

            'as_datetime' => '20-01-01 16:30:25',
            'as_format' => '1577896225',
            'as_timezone' => '20-01-01 13:30:25',

            'as_date' => '2020-01-01',
            'as_time' => '16:30:25',
        ]);

        $current = Carbon::create(2020, 1, 1, 16, 30, 25);

        $this->assertNull($fluent->date('as_null'));
        $this->assertNull($fluent->date('doesnt_exists'));

        $this->assertEquals($current, $fluent->date('as_datetime'));
        $this->assertEquals($current->format('Y-m-d H:i:s P'), $fluent->date('as_format', 'U')->format('Y-m-d H:i:s P'));
        $this->assertEquals($current, $fluent->date('as_timezone', null, 'America/Santiago'));

        $this->assertTrue($fluent->date('as_date')->isSameDay($current));
        $this->assertTrue($fluent->date('as_time')->isSameSecond('16:30:25'));
    }

    public function testDateMethodExceptionWhenValueInvalid()
    {
        $this->expectException(InvalidArgumentException::class);

        $fluent = new Fluent([
            'date' => 'invalid',
        ]);

        $fluent->date('date');
    }

    public function testDateMethodExceptionWhenFormatInvalid()
    {
        $this->expectException(InvalidArgumentException::class);

        $fluent = new Fluent([
            'date' => '20-01-01 16:30:25',
        ]);

        $fluent->date('date', 'invalid_format');
    }

    public function testEnumMethod()
    {
        $fluent = new Fluent([
            'valid_enum_value' => 'A',
            'invalid_enum_value' => 'invalid',
            'empty_value_request' => '',
            'string' => [
                'a' => '1',
                'b' => '2',
                'doesnt_exist' => '-1024',
            ],
            'int' => [
                'a' => 1,
                'b' => 2,
                'doesnt_exist' => 1024,
            ],
        ]);

        $this->assertNull($fluent->enum('doesnt_exist', TestEnum::class));

        $this->assertEquals(TestStringBackedEnum::A, $fluent->enum('valid_enum_value', TestStringBackedEnum::class));

        $this->assertNull($fluent->enum('invalid_enum_value', TestStringBackedEnum::class));
        $this->assertNull($fluent->enum('empty_value_request', TestStringBackedEnum::class));
        $this->assertNull($fluent->enum('valid_enum_value', TestEnum::class));

        // $this->assertEquals(TestBackedEnum::A, $fluent->enum('string.a', TestBackedEnum::class));
        // $this->assertEquals(TestBackedEnum::B, $fluent->enum('string.b', TestBackedEnum::class));
        // $this->assertNull($fluent->enum('string.doesnt_exist', TestBackedEnum::class));
        $this->assertEquals(TestBackedEnum::A, $fluent->enum('int.a', TestBackedEnum::class));
        $this->assertEquals(TestBackedEnum::B, $fluent->enum('int.b', TestBackedEnum::class));
        $this->assertNull($fluent->enum('int.doesnt_exist', TestBackedEnum::class));
    }

    public function testEnumsMethod()
    {
        $fluent = new Fluent([
            'valid_enum_values' => ['A', 'B'],
            'invalid_enum_values' => ['invalid', 'invalid'],
            'empty_value_request' => [],
            'string' => [
                'a' => ['1', '2'],
                'b' => '2',
                'doesnt_exist' => '-1024',
            ],
            'int' => [
                'a' => [1, 2],
                'b' => 2,
                'doesnt_exist' => 1024,
            ],
        ]);

        $this->assertEmpty($fluent->enums('doesnt_exist', TestEnum::class));

        $this->assertEquals([TestStringBackedEnum::A, TestStringBackedEnum::B], $fluent->enums('valid_enum_values', TestStringBackedEnum::class));

        $this->assertEmpty($fluent->enums('invalid_enum_value', TestStringBackedEnum::class));
        $this->assertEmpty($fluent->enums('empty_value_request', TestStringBackedEnum::class));
        $this->assertEmpty($fluent->enums('valid_enum_value', TestEnum::class));

        // $this->assertEquals([TestBackedEnum::A, TestBackedEnum::B], $fluent->enums('string.a', TestBackedEnum::class));
        // $this->assertEquals([TestBackedEnum::B], $fluent->enums('string.b', TestBackedEnum::class));
        // $this->assertEmpty($fluent->enums('string.doesnt_exist', TestBackedEnum::class));

        $this->assertEquals([TestBackedEnum::A, TestBackedEnum::B], $fluent->enums('int.a', TestBackedEnum::class));
        $this->assertEquals([TestBackedEnum::B], $fluent->enums('int.b', TestBackedEnum::class));
        $this->assertEmpty($fluent->enums('int.doesnt_exist', TestBackedEnum::class));
    }

    public function testFill()
    {
        $fluent = new Fluent(['name' => 'John Doe']);

        $fluent->fill([
            'email' => 'john.doe@example.com',
            'age' => 30,
        ]);

        $this->assertEquals([
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'age' => 30,
        ], $fluent->getAttributes());
    }

    public function testMacroable()
    {
        Fluent::macro('foo', function () {
            return $this->fill([
                'foo' => 'bar',
                'baz' => 'zal',
            ]);
        });

        $fluent = new Fluent([
            'bee' => 'ser',
        ]);

        $this->assertSame([
            'bee' => 'ser',
            'foo' => 'bar',
            'baz' => 'zal',
        ], $fluent->foo()->all());
    }

    public function testIsEmpty()
    {
        $fluent = new Fluent();
        $this->assertTrue($fluent->isEmpty());

        $fluent = new Fluent([]);
        $this->assertTrue($fluent->isEmpty());

        $fluent = new Fluent(['key' => 'value']);
        $this->assertFalse($fluent->isEmpty());

        $fluent = new Fluent(['key' => null]);
        $this->assertFalse($fluent->isEmpty());

        $fluent = new Fluent(['key' => '']);
        $this->assertFalse($fluent->isEmpty());

        $fluent = new Fluent(['key' => 0]);
        $this->assertFalse($fluent->isEmpty());
    }

    public function testIsNotEmpty()
    {
        $fluent = new Fluent();
        $this->assertFalse($fluent->isNotEmpty());

        $fluent = new Fluent([]);
        $this->assertFalse($fluent->isNotEmpty());

        $fluent = new Fluent(['key' => 'value']);
        $this->assertTrue($fluent->isNotEmpty());

        $fluent = new Fluent(['key' => null]);
        $this->assertTrue($fluent->isNotEmpty());

        $fluent = new Fluent(['key' => '']);
        $this->assertTrue($fluent->isNotEmpty());

        $fluent = new Fluent(['key' => 0]);
        $this->assertTrue($fluent->isNotEmpty());
    }

    public function testIsEmptyWithDynamicSetters()
    {
        $fluent = new Fluent();
        $this->assertTrue($fluent->isEmpty());

        $fluent->name('John');
        $this->assertFalse($fluent->isEmpty());
        $this->assertTrue($fluent->isNotEmpty());
    }

    public function testIsEmptyWithArrayAccess()
    {
        $fluent = new Fluent();
        $this->assertTrue($fluent->isEmpty());

        $fluent['key'] = 'value';
        $this->assertFalse($fluent->isEmpty());
        $this->assertTrue($fluent->isNotEmpty());

        unset($fluent['key']);
        $this->assertTrue($fluent->isEmpty());
        $this->assertFalse($fluent->isNotEmpty());
    }

    public function testConstructor()
    {
        $fluent = new Fluent();
        $this->assertEquals([], $fluent->getAttributes());

        $fluent = new Fluent(['name' => 'John', 'age' => 30]);
        $this->assertEquals(['name' => 'John', 'age' => 30], $fluent->getAttributes());

        $object = new stdClass();
        $object->name = 'Jane';
        $object->age = 25;
        $fluent = new Fluent($object);
        $this->assertEquals(['name' => 'Jane', 'age' => 25], $fluent->getAttributes());

        $generator = function () {
            yield 'key1' => 'value1';
            yield 'key2' => 'value2';
        };
        $fluent = new Fluent($generator());
        $this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $fluent->getAttributes());
    }

    public function testMagicCall()
    {
        $fluent = new Fluent();

        $result = $fluent->name('John');
        $this->assertSame($fluent, $result);
        $this->assertEquals('John', $fluent->getAttributes()['name']);

        $fluent->isActive();
        $this->assertTrue($fluent->getAttributes()['isActive']);

        $fluent->count(5);
        $this->assertEquals(5, $fluent->getAttributes()['count']);

        $fluent->setValue(null);
        $this->assertNull($fluent->getAttributes()['setValue']);
    }

    public function testMagicGet()
    {
        $fluent = new Fluent(['name' => 'John', 'age' => 30]);

        $this->assertEquals('John', $fluent->name);
        $this->assertEquals(30, $fluent->age);
        $this->assertNull($fluent->nonexistent);
    }

    public function testMagicSet()
    {
        $fluent = new Fluent();

        $fluent->name = 'John';
        $this->assertEquals('John', $fluent->getAttributes()['name']);

        $fluent->age = 30;
        $this->assertEquals(30, $fluent->getAttributes()['age']);

        $fluent->value = null;
        $this->assertNull($fluent->getAttributes()['value']);
    }

    public function testMagicIsset()
    {
        $fluent = new Fluent(['name' => 'John', 'value' => null]);

        $this->assertTrue(isset($fluent->name));
        $this->assertFalse(isset($fluent->value));
        $this->assertFalse(isset($fluent->nonexistent));
    }

    public function testMagicUnset()
    {
        $fluent = new Fluent(['name' => 'John', 'age' => 30]);

        unset($fluent->name);
        $this->assertFalse(array_key_exists('name', $fluent->getAttributes()));
        $this->assertTrue(array_key_exists('age', $fluent->getAttributes()));
    }

    public function testToString()
    {
        $fluent = new Fluent(['name' => 'John', 'age' => 30]);
        $expected = json_encode(['name' => 'John', 'age' => 30]);

        $this->assertEquals($expected, (string) $fluent);
        $this->assertEquals($expected, $fluent->__toString());
    }

    public function testGet()
    {
        $fluent = new Fluent(['name' => 'John', 'value' => null]);

        $this->assertEquals('John', $fluent->get('name'));
        $this->assertNull($fluent->get('value'));
        $this->assertNull($fluent->get('nonexistent'));
        $this->assertEquals('default', $fluent->get('nonexistent', 'default'));

        $closure = function () {
            return 'closure_result';
        };
        $this->assertEquals('closure_result', $fluent->get('nonexistent', $closure));

        $this->assertEquals('John', $fluent->get('name', 'default'));
    }

    public function testGetAttributes()
    {
        $attributes = ['name' => 'John', 'age' => 30];
        $fluent = new Fluent($attributes);

        $this->assertEquals($attributes, $fluent->getAttributes());
        $this->assertSame($fluent->getAttributes(), $fluent->getAttributes());
    }

    public function testToArray()
    {
        $attributes = ['name' => 'John', 'age' => 30];
        $fluent = new Fluent($attributes);

        $this->assertEquals($attributes, $fluent->toArray());
        $this->assertSame($fluent->getAttributes(), $fluent->toArray());
    }

    public function testJsonSerialize()
    {
        $attributes = ['name' => 'John', 'age' => 30];
        $fluent = new Fluent($attributes);

        $this->assertEquals($attributes, $fluent->jsonSerialize());
        $this->assertEquals($attributes, json_decode(json_encode($fluent), true));
    }

    public function testToJson()
    {
        $attributes = ['name' => 'John', 'age' => 30];
        $fluent = new Fluent($attributes);
        $expected = json_encode($attributes);

        $this->assertEquals($expected, $fluent->toJson());
        $this->assertEquals($expected, $fluent->toJson(0));

        $expectedPretty = json_encode($attributes, JSON_PRETTY_PRINT);
        $this->assertEquals($expectedPretty, $fluent->toJson(JSON_PRETTY_PRINT));
    }

    public function testOffsetExists()
    {
        $fluent = new Fluent(['name' => 'John', 'value' => null]);

        $this->assertTrue($fluent->offsetExists('name'));
        $this->assertFalse($fluent->offsetExists('value'));
        $this->assertFalse($fluent->offsetExists('nonexistent'));

        $this->assertTrue(isset($fluent['name']));
        $this->assertFalse(isset($fluent['value']));
        $this->assertFalse(isset($fluent['nonexistent']));
    }

    public function testOffsetGet()
    {
        $fluent = new Fluent(['name' => 'John', 'value' => null]);

        $this->assertEquals('John', $fluent->offsetGet('name'));
        $this->assertNull($fluent->offsetGet('value'));
        $this->assertNull($fluent->offsetGet('nonexistent'));

        $this->assertEquals('John', $fluent['name']);
        $this->assertNull($fluent['value']);
        $this->assertNull($fluent['nonexistent']);
    }

    public function testOffsetSet()
    {
        $fluent = new Fluent();

        $fluent->offsetSet('name', 'John');
        $this->assertEquals('John', $fluent->getAttributes()['name']);

        $fluent->offsetSet('value', null);
        $this->assertNull($fluent->getAttributes()['value']);

        $fluent['age'] = 30;
        $this->assertEquals(30, $fluent->getAttributes()['age']);

        $fluent[] = 'array_value';
        $this->assertEquals('array_value', $fluent->getAttributes()[null]);
    }

    public function testOffsetUnset()
    {
        $fluent = new Fluent(['name' => 'John', 'age' => 30]);

        $fluent->offsetUnset('name');
        $this->assertFalse(array_key_exists('name', $fluent->getAttributes()));
        $this->assertTrue(array_key_exists('age', $fluent->getAttributes()));

        unset($fluent['age']);
        $this->assertFalse(array_key_exists('age', $fluent->getAttributes()));
    }

    public function testChainableMethods()
    {
        $fluent = new Fluent();

        $result = $fluent
            ->name('John')
            ->age(30)
            ->isActive(true);

        $this->assertSame($fluent, $result);
        $this->assertEquals([
            'name' => 'John',
            'age' => 30,
            'isActive' => true,
        ], $fluent->getAttributes());
    }

    public function testComplexDataTypes()
    {
        $object = new stdClass();
        $object->nested = 'value';

        $fluent = new Fluent([
            'array' => [1, 2, 3],
            'object' => $object,
            'closure' => function () {
                return 'test';
            },
            'null' => null,
            'boolean' => true,
            'integer' => 42,
            'float' => 3.14,
        ]);

        $this->assertEquals([1, 2, 3], $fluent->get('array'));
        $this->assertSame($object, $fluent->get('object'));
        $this->assertInstanceOf('Closure', $fluent->get('closure'));
        $this->assertNull($fluent->get('null'));
        $this->assertTrue($fluent->get('boolean'));
        $this->assertEquals(42, $fluent->get('integer'));
        $this->assertEquals(3.14, $fluent->get('float'));
    }

    public function testMacro()
    {
        Fluent::macro('uppercase', function ($value) {
            return strtoupper($value);
        });

        $fluent = new Fluent(['name' => 'john']);
        $result = $fluent->uppercase($fluent->get('name'));

        $this->assertEquals('JOHN', $result);
    }

    public function testMacroWithThis()
    {
        Fluent::macro('getUppercaseName', function () {
            return strtoupper($this->get('name'));
        });

        $fluent = new Fluent(['name' => 'john']);
        $result = $fluent->getUppercaseName();

        $this->assertEquals('JOHN', $result);
    }

    public function testMacroChaining()
    {
        Fluent::macro('setAndReturn', function ($key, $value) {
            $this->attributes[$key] = $value;
            return $this;
        });

        $fluent = new Fluent();
        $result = $fluent->setAndReturn('name', 'john')->setAndReturn('age', 30);

        $this->assertSame($fluent, $result);
        $this->assertEquals(['name' => 'john', 'age' => 30], $fluent->getAttributes());
    }

    public function testHasMacro()
    {
        $this->assertFalse(Fluent::hasMacro('customMethod'));

        Fluent::macro('customMethod', function () {
            return 'custom';
        });

        $this->assertTrue(Fluent::hasMacro('customMethod'));
    }

    public function testMacroOverridesDynamicCall()
    {
        Fluent::macro('name', function ($value = null) {
            if ($value === null) {
                return 'macro called';
            }
            return 'macro with value: ' . $value;
        });

        $fluent = new Fluent();
        $result = $fluent->name();

        $this->assertEquals('macro called', $result);

        $result = $fluent->name('test');
        $this->assertEquals('macro with value: test', $result);

        $fluent = new Fluent();
        $fluent->age(25);
        $this->assertEquals(25, $fluent->get('age'));
    }

    public function testMixin()
    {
        $mixin = new class {
            public function getFullName()
            {
                return function () {
                    return $this->get('first_name') . ' ' . $this->get('last_name');
                };
            }

            public function setFullName()
            {
                return function ($firstName, $lastName) {
                    $this->attributes['first_name'] = $firstName;
                    $this->attributes['last_name'] = $lastName;
                    return $this;
                };
            }
        };

        Fluent::mixin($mixin);

        $fluent = new Fluent();
        $result = $fluent->setFullName('John', 'Doe');

        $this->assertSame($fluent, $result);
        $this->assertEquals('John Doe', $fluent->getFullName());
        $this->assertEquals(['first_name' => 'John', 'last_name' => 'Doe'], $fluent->getAttributes());
    }

    public function testFluentIsIterable()
    {
        $fluent = new Fluent([
            'name' => 'Taylor',
            'role' => 'admin',
        ]);

        $result = [];

        foreach ($fluent as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertSame([
            'name' => 'Taylor',
            'role' => 'admin',
        ], $result);
    }
}

class FluentArrayIteratorStub implements IteratorAggregate
{
    protected $items = [];

    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }
}
