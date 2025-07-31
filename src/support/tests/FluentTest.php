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

use Hyperf\Support\Fluent;
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
}
