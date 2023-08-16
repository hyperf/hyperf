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

use ArrayObject;
use Hyperf\Support\Optional;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;

use function Hyperf\Support\optional;

/**
 * @internal
 * @coversNothing
 */
class OptionalTest extends TestCase
{
    public function testGetExistItemOnObject()
    {
        $expected = 'test';

        $targetObj = new stdClass();
        $targetObj->item = $expected;

        $optional = new Optional($targetObj);

        $this->assertEquals($expected, $optional->item);
    }

    public function testGetNotExistItemOnObject()
    {
        $targetObj = new stdClass();

        $optional = new Optional($targetObj);

        $this->assertNull($optional->item);
    }

    public function testIssetExistItemOnObject()
    {
        $targetObj = new stdClass();
        $targetObj->item = '';

        $optional = new Optional($targetObj);

        $this->assertTrue(isset($optional->item));
    }

    public function testIssetNotExistItemOnObject()
    {
        $targetObj = new stdClass();

        $optional = new Optional($targetObj);

        $this->assertFalse(isset($optional->item));
        $this->assertFalse(isset($targetObj->item));
    }

    public function testGetExistItemOnArray()
    {
        $expected = 'test';

        $targetArr = [
            'item' => $expected,
        ];

        $optional = new Optional($targetArr);

        $this->assertEquals($expected, $optional['item']);
    }

    public function testGetNotExistItemOnArray()
    {
        $targetObj = [];

        $optional = new Optional($targetObj);

        $this->assertNull($optional['item']);
    }

    public function testIssetExistItemOnArray()
    {
        $targetArr = [
            'item' => '',
        ];

        $optional = new Optional($targetArr);

        $this->assertTrue(isset($optional['item']));
        $this->assertFalse(isset($optional->item));
    }

    public function testIssetNotExistItemOnArray()
    {
        $targetArr = [];

        $optional = new Optional($targetArr);

        $this->assertFalse(isset($optional['item']));
        $this->assertFalse(isset($optional->item));
    }

    public function testIssetExistItemOnNull()
    {
        $targetNull = null;

        $optional = new Optional($targetNull);

        $this->assertFalse(isset($optional->item));
    }

    public function testArrayObject()
    {
        $obj = new ArrayObject(['id' => $id = uniqid()]);

        $optional = new Optional($obj);

        $this->assertTrue(isset($optional['id']));
        $this->assertFalse(isset($optional->id));
        $this->assertFalse(isset($obj->id));

        $this->assertFalse(isset($optional['name']));
        $this->assertFalse(isset($optional->name));
        $this->assertFalse(isset($obj->name));

        $this->assertSame($id, $optional['id']);
    }

    public function testArrayObjectWithAsProps()
    {
        $obj = new ArrayObject(['id' => $id = uniqid()]);
        $obj->setFlags(ArrayObject::ARRAY_AS_PROPS);

        $optional = new Optional($obj);

        $this->assertTrue(isset($optional['id']));
        $this->assertTrue(isset($optional->id));
        $this->assertTrue(isset($obj->id));

        $this->assertFalse(isset($optional['name']));
        $this->assertFalse(isset($optional->name));
        $this->assertFalse(isset($obj->name));

        $this->assertSame($id, $optional['id']);
    }

    public function testOptional()
    {
        $this->assertNull(optional(null)->something());

        $this->assertEquals(10, optional(new class() {
            public function something()
            {
                return 10;
            }
        })->something());
    }

    public function testOptionalWithCallback()
    {
        $this->assertNull(optional(null, function () {
            throw new RuntimeException(
                'The optional callback should not be called for null'
            );
        }));

        $this->assertEquals(10, optional(5, function ($number) {
            return $number * 2;
        }));
    }

    public function testOptionalWithArray()
    {
        $this->assertSame('here', optional(['present' => 'here'])['present']);
        $this->assertNull(optional(null)['missing']);
        $this->assertNull(optional(['present' => 'here'])->missing);
        $this->assertNull(optional(['present' => 'here'])->present);
    }

    public function testOptionalReturnsObjectPropertyOrNull()
    {
        $this->assertSame('bar', optional((object) ['foo' => 'bar'])->foo);
        $this->assertNull(optional(['foo' => 'bar'])->foo);
        $this->assertNull(optional((object) ['foo' => 'bar'])->bar);
    }

    public function testOptionalDeterminesWhetherKeyIsSet()
    {
        $this->assertTrue(isset(optional(['foo' => 'bar'])['foo']));
        $this->assertFalse(isset(optional(['foo' => 'bar'])['bar']));
        $this->assertFalse(isset(optional()['bar']));
    }

    public function testOptionalAllowsToSetKey()
    {
        $optional = optional([]);
        $optional['foo'] = 'bar';
        $this->assertSame('bar', $optional['foo']);

        $optional = optional(null);
        $optional['foo'] = 'bar';
        $this->assertFalse(isset($optional['foo']));
    }

    public function testOptionalAllowToUnsetKey()
    {
        $optional = optional(['foo' => 'bar']);
        $this->assertTrue(isset($optional['foo']));
        unset($optional['foo']);
        $this->assertFalse(isset($optional['foo']));

        $optional = optional((object) ['foo' => 'bar']);
        $this->assertFalse(isset($optional['foo']));
        $optional['foo'] = 'bar';
        $this->assertFalse(isset($optional['foo']));
    }

    public function testOptionalIsMacroable()
    {
        Optional::macro('present', function () {
            if (is_object($this->value)) {
                return $this->value->present();
            }

            return new Optional(null);
        });

        $this->assertNull(optional(null)->present()->something());

        $this->assertSame('$10.00', optional(new class() {
            public function present()
            {
                return new class() {
                    public function something()
                    {
                        return '$10.00';
                    }
                };
            }
        })->present()->something());
    }
}
