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

namespace HyperfTest\CircuitBreaker\Cases;

use Hyperf\CircuitBreaker\State;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class StateTest extends TestCase
{
    public function testStateIsInitiallyClosed()
    {
        $state = new State();
        $this->assertTrue($state->isClose());
    }

    public function testOpenSetsStateToOpen()
    {
        $state = new State();
        $state->open();
        $this->assertTrue($state->isOpen());
    }

    public function testCloseSetsStateToClose()
    {
        $state = new State();
        $state->close();
        $this->assertTrue($state->isClose());
    }

    public function testHalfOpenSetsStateToHalfOpen()
    {
        $state = new State();
        $state->halfOpen();
        $this->assertTrue($state->isHalfOpen());
    }

    public function testStateTransitionsCorrectly()
    {
        $state = new State();
        $state->open();
        $this->assertTrue($state->isOpen());
        $state->halfOpen();
        $this->assertTrue($state->isHalfOpen());
        $state->close();
        $this->assertTrue($state->isClose());
    }
}
