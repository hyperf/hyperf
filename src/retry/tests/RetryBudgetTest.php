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

namespace HyperfTest\Retry;

use Hyperf\Retry\RetryBudget;
use Mockery;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Swoole\Coroutine\System;
use Swoole\Timer;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
/**
 * @internal
 * @coversNothing
 */
class RetryBudgetTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Timer::clearAll();
    }

    public function testRetryBudget()
    {
        $budget = new RetryBudget(
            10,
            0,
            1
        );
        $budget->produce();
        $budget->produce();
        $this->assertTrue($budget->consume());
        $this->assertTrue($budget->consume());
        $this->assertTrue(! $budget->consume());
        $budget = new RetryBudget(
            10,
            0,
            0.5
        );
        $budget->produce();
        $budget->produce();
        $this->assertTrue($budget->consume());
        $this->assertTrue(! $budget->consume());
        $budget = new RetryBudget(
            10,
            1,
            1
        );
        $this->assertTrue($budget->consume());
        $this->assertTrue(! $budget->consume());
        $budget = new RetryBudget(
            100,
            2,
            0.1
        );
        $this->assertTrue($budget->consume());
        System::sleep(1.2);
        $this->assertTrue($budget->consume());
        $this->assertTrue($budget->consume());
        $this->assertTrue($budget->consume());
        $this->assertTrue(! $budget->consume());

        // Retry budget should never have more than 1 token in this test
        $budget = new RetryBudget(
            1,
            1,
            1
        );
        $budget->init();
        $ref = new ReflectionClass(RetryBudget::class);
        $prop = $ref->getProperty('budget');
        System::sleep(1.2);
        $this->assertLessThanOrEqual(1, $prop->getValue($budget)->count());
        System::sleep(1.2);
        $this->assertLessThanOrEqual(1, $prop->getValue($budget)->count());
    }
}
