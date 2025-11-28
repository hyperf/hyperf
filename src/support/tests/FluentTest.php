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
use HyperfTest\Support\Stub\FooEnum;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class FluentTest extends TestCase
{
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
            'closure' => function () { return 'test'; },
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

    public function testGetIterator()
    {
        $fluent = new Fluent(['name' => 'John', 'age' => 30]);
        $iterator = $fluent->getIterator();

        $this->assertInstanceOf(ArrayIterator::class, $iterator);
        $this->assertEquals(['name' => 'John', 'age' => 30], iterator_to_array($iterator));
    }

    public function testSet()
    {
        $fluent = new Fluent();

        $result = $fluent->set('name', 'John');
        $this->assertSame($fluent, $result);
        $this->assertEquals('John', $fluent->get('name'));

        $fluent->set('user.profile.name', 'Jane');
        $this->assertEquals('Jane', $fluent->get('user.profile.name'));

        $fluent->set('level', 0);
        $this->assertEquals(0, $fluent->get('level'));
    }

    public function testFill()
    {
        $fluent = new Fluent();

        $result = $fluent->fill(['name' => 'John', 'age' => 30]);
        $this->assertSame($fluent, $result);
        $this->assertEquals(['name' => 'John', 'age' => 30], $fluent->getAttributes());

        $fluent->fill(['city' => 'New York']);
        $this->assertEquals(['name' => 'John', 'age' => 30, 'city' => 'New York'], $fluent->getAttributes());
    }

    public function testValue()
    {
        $fluent = new Fluent(['name' => 'John', 'value' => null]);

        $this->assertEquals('John', $fluent->value('name'));
        $this->assertNull($fluent->value('value'));
        $this->assertNull($fluent->value('nonexistent'));
        $this->assertEquals('default', $fluent->value('nonexistent', 'default'));

        $closure = function () {
            return 'closure_result';
        };
        $this->assertEquals('closure_result', $fluent->value('nonexistent', $closure));
    }

    public function testScope()
    {
        $fluent = new Fluent([
            'user' => [
                'name' => 'John',
                'profile' => [
                    'email' => 'john@example.com',
                    'age' => 30,
                ],
            ],
        ]);

        $userScope = $fluent->scope('user');
        $this->assertInstanceOf(Fluent::class, $userScope);
        $this->assertEquals('John', $userScope->get('name'));
        $this->assertEquals(['email' => 'john@example.com', 'age' => 30], $userScope->get('profile'));

        $defaultScope = $fluent->scope('nonexistent', ['default' => 'value']);
        $this->assertEquals(['default' => 'value'], $defaultScope->getAttributes());
    }

    public function testAll()
    {
        $attributes = ['name' => 'John', 'age' => 30, 'city' => 'New York'];
        $fluent = new Fluent($attributes);

        $this->assertEquals($attributes, $fluent->all());

        $subset = $fluent->all(['name', 'age']);
        $this->assertEquals(['name' => 'John', 'age' => 30], $subset);

        $nested = $fluent->all('name', 'age');
        $this->assertEquals(['name' => 'John', 'age' => 30], $nested);
    }

    public function testInteractsWithDataMethods()
    {
        $fluent = new Fluent([
            'name' => 'John',
            'email' => 'john@example.com',
            'phone' => '',
            'age' => 30,
            'active' => true,
            'score' => 95.5,
            'tags' => ['developer', 'php'],
            'profile' => [
                'bio' => 'A developer',
                'city' => 'New York',
            ],
        ]);

        // Test exists/has
        $this->assertTrue($fluent->exists('name'));
        $this->assertTrue($fluent->has('name'));
        $this->assertTrue($fluent->has(['name', 'email']));
        $this->assertFalse($fluent->has('nonexistent'));
        $this->assertFalse($fluent->has(['name', 'nonexistent']));

        // Test hasAny
        $this->assertTrue($fluent->hasAny(['name', 'nonexistent']));
        $this->assertFalse($fluent->hasAny(['nonexistent', 'missing']));

        // Test filled
        $this->assertTrue($fluent->filled('name'));
        $this->assertFalse($fluent->filled('phone')); // empty string
        $this->assertTrue($fluent->filled(['name', 'email']));
        $this->assertFalse($fluent->filled(['name', 'phone']));

        // Test isNotFilled
        $this->assertFalse($fluent->isNotFilled('name'));
        $this->assertTrue($fluent->isNotFilled('phone'));
        $this->assertTrue($fluent->isNotFilled(['phone']));
        $this->assertFalse($fluent->isNotFilled(['name', 'phone']));

        // Test anyFilled
        $this->assertTrue($fluent->anyFilled(['name', 'phone']));
        $this->assertFalse($fluent->anyFilled(['phone']));

        // Test missing
        $this->assertFalse($fluent->missing('name'));
        $this->assertTrue($fluent->missing('nonexistent'));
        $this->assertTrue($fluent->missing(['nonexistent', 'missing']));

        // Test string
        $this->assertInstanceOf(Stringable::class, $fluent->string('name'));
        $this->assertEquals('John', (string) $fluent->string('name'));
        $this->assertEquals('default', (string) $fluent->string('nonexistent', 'default'));

        // Test str (alias for string)
        $this->assertInstanceOf(Stringable::class, $fluent->str('name'));
        $this->assertEquals('John', (string) $fluent->str('name'));

        // Test boolean
        $this->assertTrue($fluent->boolean('active'));
        $this->assertFalse($fluent->boolean('nonexistent'));
        $this->assertTrue($fluent->boolean('nonexistent', true));

        // Test integer
        $this->assertEquals(30, $fluent->integer('age'));
        $this->assertEquals(0, $fluent->integer('nonexistent'));
        $this->assertEquals(25, $fluent->integer('nonexistent', 25));

        // Test float
        $this->assertEquals(95.5, $fluent->float('score'));
        $this->assertEquals(0.0, $fluent->float('nonexistent'));
        $this->assertEquals(100.0, $fluent->float('nonexistent', 100.0));

        // Test array
        $this->assertEquals(['developer', 'php'], $fluent->array('tags'));
        $this->assertEquals(['name' => 'John'], $fluent->array(['name']));
        $this->assertEquals([], $fluent->array('nonexistent'));

        // Test collect
        $collection = $fluent->collect('tags');
        $this->assertInstanceOf(Collection::class, $collection);
        $this->assertEquals(['developer', 'php'], $collection->toArray());

        // Test only
        $only = $fluent->only(['name', 'email', 'nonexistent']);
        $this->assertEquals(['name' => 'John', 'email' => 'john@example.com'], $only);

        // Test except
        $except = $fluent->except(['phone', 'age']);
        $expected = [
            'name' => 'John',
            'email' => 'john@example.com',
            'active' => true,
            'score' => 95.5,
            'tags' => ['developer', 'php'],
            'profile' => [
                'bio' => 'A developer',
                'city' => 'New York',
            ],
        ];
        $this->assertEquals($expected, $except);
    }

    public function testWhenMethods()
    {
        $fluent = new Fluent(['name' => 'John', 'email' => '', 'age' => 30]);

        // Test whenHas
        $result = $fluent->whenHas('name', function ($value) {
            return 'Hello ' . $value;
        });
        $this->assertEquals('Hello John', $result);

        $result = $fluent->whenHas('nonexistent', function () {
            return 'exists';
        }, function () {
            return 'default';
        });
        $this->assertEquals('default', $result);

        // Test whenFilled
        $result = $fluent->whenFilled('name', function ($value) {
            return 'Filled: ' . $value;
        });
        $this->assertEquals('Filled: John', $result);

        $result = $fluent->whenFilled('email', function () {
            return 'filled';
        }, function () {
            return 'not filled';
        });
        $this->assertEquals('not filled', $result);

        // Test whenMissing
        $result = $fluent->whenMissing('nonexistent', function () {
            return 'missing';
        });
        $this->assertEquals('missing', $result);

        $result = $fluent->whenMissing('name', function () {
            return 'missing';
        }, function () {
            return 'exists';
        });
        $this->assertEquals('exists', $result);
    }

    public function testDateMethod()
    {
        $fluent = new Fluent([
            'created_at' => '2023-01-15 10:30:00',
            'formatted_date' => '15/01/2023',
            'empty_date' => '',
            'null_date' => null,
        ]);

        // Test basic date parsing
        $date = $fluent->date('created_at');
        $this->assertInstanceOf(Carbon::class, $date);
        $this->assertEquals('2023-01-15 10:30:00', $date->format('Y-m-d H:i:s'));

        // Test date with format
        $date = $fluent->date('formatted_date', 'd/m/Y');
        $this->assertInstanceOf(Carbon::class, $date);
        $this->assertEquals('2023-01-15', $date->format('Y-m-d'));

        // Test empty date
        $this->assertNull($fluent->date('empty_date'));
        $this->assertNull($fluent->date('null_date'));
        $this->assertNull($fluent->date('nonexistent'));
    }

    public function testEnumMethods()
    {
        $fluent = new Fluent([
            'status' => 'active',
            'statuses' => ['active', 'inactive', 'pending'],
            'invalid_status' => 'invalid',
            'empty_status' => '',
            'zero_status' => 0,
        ]);

        // Test enum method when enum class doesn't exist (should return default)
        $result = $fluent->enum('status', 'NonExistentEnum', 'default_value');
        $this->assertEquals('default_value', $result);

        // Test enum method with empty value (should return default)
        $result = $fluent->enum('empty_status', 'NonExistentEnum', 'default_value');
        $this->assertEquals('default_value', $result);

        // Test enum method with nonexistent key (should return default)
        $result = $fluent->enum('nonexistent', 'NonExistentEnum', 'default_value');
        $this->assertEquals('default_value', $result);

        // Test enums method when enum class doesn't exist (should return empty array)
        $result = $fluent->enums('statuses', 'NonExistentEnum');
        $this->assertEquals([], $result);

        // Test enums method with empty value (should return empty array)
        $result = $fluent->enums('empty_status', 'NonExistentEnum');
        $this->assertEquals([], $result);

        // Test enum method with nonexistent key (should return default)
        $result = $fluent->enum('zero_status', FooEnum::class, 'default_value');
        $this->assertEquals(FooEnum::Zero, $result);
    }
}
