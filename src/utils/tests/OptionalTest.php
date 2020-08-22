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
namespace HyperfTest\Utils;

use Hyperf\Utils\Optional;
use PHPUnit\Framework\TestCase;
use stdClass;

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
        $obj = new \ArrayObject(['id' => $id = uniqid()]);

        $optional = new Optional($obj);

        $this->assertTrue(isset($optional['id']));
        $this->assertFalse(isset($optional->id));
        $this->assertFalse(isset($obj->id));

        $this->assertFalse(isset($optional['name']));
        $this->assertFalse(isset($optional->name));
        $this->assertFalse(isset($obj->name));

        $this->assertSame($id, $optional['id']);
    }
}
