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

use Hyperf\Engine\Exception\CoroutineDestroyedException;
use Hyperf\Utils\Coroutine;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CoroutineTest extends TestCase
{
    public function testCoroutineParentId()
    {
        $pid = Coroutine::id();
        Coroutine::create(function () use ($pid) {
            $this->assertSame($pid, Coroutine::parentId());
            $pid = Coroutine::id();
            $id = Coroutine::create(function () use ($pid) {
                $this->assertSame($pid, Coroutine::parentId(Coroutine::id()));
                usleep(1000);
            });
            Coroutine::create(function () use ($pid) {
                $this->assertSame($pid, Coroutine::parentId());
            });
            $this->assertSame($pid, Coroutine::parentId($id));
        });
    }

    public function testCoroutineParentIdHasBeenDestroyed()
    {
        $id = Coroutine::create(function () {
        });

        try {
            Coroutine::parentId($id);
            $this->assertTrue(false);
        } catch (\Throwable $exception) {
            $this->assertInstanceOf(CoroutineDestroyedException::class, $exception);
        }
    }

    /**
     * @group NonCoroutine
     */
    public function testCoroutineInTopCoroutine()
    {
        run(function () {
            $this->assertSame(0, Coroutine::parentId());
        });
    }
}
