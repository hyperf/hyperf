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
}