<?php


namespace HyperfTest\Retry;


use Hyperf\Retry\Annotation\RetryFalsy;
use PHPUnit\Framework\TestCase;

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
