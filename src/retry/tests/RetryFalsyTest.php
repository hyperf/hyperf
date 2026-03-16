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

use Hyperf\Retry\Annotation\RetryFalsy;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class RetryFalsyTest extends TestCase
{
    public function testIsFalsy()
    {
        $this->assertTrue(RetryFalsy::isFalsy(false));
        $this->assertTrue(RetryFalsy::isFalsy(''));
        $this->assertTrue(RetryFalsy::isFalsy(null));
        $this->assertTrue(RetryFalsy::isFalsy(0));

        $this->assertFalse(RetryFalsy::isFalsy('ok'));
        $this->assertFalse(RetryFalsy::isFalsy(true));
        $this->assertFalse(RetryFalsy::isFalsy(1));
    }
}
