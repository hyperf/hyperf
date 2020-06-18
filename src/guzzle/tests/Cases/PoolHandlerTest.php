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
namespace HyperfTest\Guzzle\Cases;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class PoolHandlerTest extends TestCase
{
    protected $id = 0;

    public function testTryFinally()
    {
        $this->get();

        $this->assertSame(2, $this->id);
    }

    protected function get()
    {
        try {
            $this->id = 1;
            return;
        } finally {
            $this->id = 2;
        }
    }
}
