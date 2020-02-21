<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace HyperfTest\Rpc;

use Hyperf\Rpc\Context;
use Hyperf\Utils\Str;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ContextTest extends TestCase
{
    protected function tearDown()
    {
        (new Context())->clear();
    }

    public function testSetDataAndGetData()
    {
        $context = new Context();
        $context->setData([
            'id' => $id = uniqid(),
            'name' => $name = Str::random(8),
        ]);

        $context2 = new Context();
        $this->assertSame([
            'id' => $id,
            'name' => $name,
        ], $context->getData());
        $this->assertSame($context->getData(), $context2->getData());

        parallel([function () use ($context) {
            $context2 = new Context();
            $this->assertSame([], $context->getData());
            $this->assertSame([], $context2->getData());
        }]);
    }

    public function testSetAndGet()
    {
        $context = new Context();
        $context->setData([
            'id' => $id = uniqid(),
            'name' => $name = Str::random(8),
        ]);

        $context->set('gender', $gender = rand(0, 1));

        $this->assertSame([
            'id' => $id,
            'name' => $name,
            'gender' => $gender,
        ], $context->getData());

        $this->assertSame($gender, $context->get('gender'));

        parallel([function () use ($context) {
            $this->assertSame(null, $context->get('gender'));
        }]);
    }
}
