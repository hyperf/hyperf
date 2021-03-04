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
namespace HyperfTest\Cache\Cases;

use Hyperf\Cache\Helper\StringHelper;
use Hyperf\Di\Aop\ProceedingJoinPoint;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class StringHelperTest extends TestCase
{
    public function testFormat()
    {
        $string = StringHelper::format('test', ['id' => 1], '_#{id}');
        $this->assertSame('test:_1', $string);

        $string = StringHelper::format('test', ['id' => 1, 'name' => 'Hyperf'], '_#{id}');
        $this->assertSame('test:_1', $string);

        $string = StringHelper::format('test', ['id' => 1, 'name' => 'Hyperf']);
        $this->assertSame('test:1:Hyperf', $string);

        $string = StringHelper::format('test', ['id' => 1, 'name' => 'Hyperf'], 'Hyperf:#{name}');
        $this->assertSame('test:Hyperf:Hyperf', $string);

        $string = StringHelper::format('test', ['this' => new class() {
            public $id = 1;
        }], '#{this.id}');
        $this->assertSame('test:1', $string);

        $class = new class($id = uniqid()) {
            public $id;

            public function __construct($id)
            {
                $this->id = $id;
            }

            public function getPoint()
            {
                return new ProceedingJoinPoint(function () {
                }, 'Foo', 'test', []);
            }
        };

        $string = StringHelper::format('test', [], '#{this.id}', $class->getPoint());
        $this->assertSame('test:' . $id, $string);
    }
}
