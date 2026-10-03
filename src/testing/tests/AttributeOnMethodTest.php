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

namespace HyperfTest\Testing;

use Hyperf\Coroutine\Coroutine;
use Hyperf\Testing\Attributes\NonCoroutine;
use Hyperf\Testing\Concerns\RunTestsInCoroutine;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[Group('NonCoroutine')]
class AttributeOnMethodTest extends TestCase
{
    use RunTestsInCoroutine;

    #[NonCoroutine]
    public function testWithNonCoroutineAttribute()
    {
        $this->assertFalse(Coroutine::inCoroutine());
    }

    public function testWithoutNonCoroutineAttribute()
    {
        $this->assertTrue(Coroutine::inCoroutine());
    }
}
