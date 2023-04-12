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
namespace HyperfTest\Coroutine;

use Exception;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\Exception\ParallelExecutionException;
use Hyperf\Coroutine\Parallel;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

use function Hyperf\Coroutine\parallel;

/**
 * @internal
 * @covers \Hyperf\Coroutine\Parallel
 */
class ParallelTest extends TestCase
{
    public function testParallel()
    {
        // Closure
        $parallel = new Parallel();
        for ($i = 0; $i < 3; ++$i) {
            $parallel->add(function () {
                return Coroutine::id();
            });
        }
        $result = $parallel->wait();
        $id = $result[0];
        $this->assertSame([$id, $id + 1, $id + 2], $result);

        // Array
        $parallel = new Parallel();
        for ($i = 0; $i < 3; ++$i) {
            $parallel->add([$this, 'returnCoId']);
        }
        $result = $parallel->wait();
        $id = $result[0];
        $this->assertSame([$id, $id + 1, $id + 2], $result);
    }

    public function testParallelConcurrent()
    {
        $parallel = new Parallel();
        $num = 0;
        $callback = function () use (&$num) {
            ++$num;
            Coroutine::sleep(0.01);
            return $num;
        };
        for ($i = 0; $i < 4; ++$i) {
            $parallel->add($callback);
        }
        $res = $parallel->wait();
        $this->assertSame([4, 4, 4, 4], array_values($res));

        $parallel = new Parallel(2);
        $num = 0;
        $callback = function () use (&$num) {
            ++$num;
            Coroutine::sleep(0.01);
            return $num;
        };
        for ($i = 0; $i < 4; ++$i) {
            $parallel->add($callback);
        }
        $res = $parallel->wait();
        sort($res);
        $this->assertSame([2, 3, 4, 4], array_values($res));

        $num = 10;
        $callbacks = [];
        for ($i = 0; $i < 4; ++$i) {
            $callbacks[] = function () use (&$num) {
                ++$num;
                Coroutine::sleep(0.01);
                return $num;
            };
        }
        $res = parallel($callbacks, 2);
        sort($res);
        $this->assertSame([12, 13, 14, 14], array_values($res));
    }

    public function testParallelCallbackCount()
    {
        $parallel = new Parallel();
        $callback = function () {
            return 1;
        };
        for ($i = 0; $i < 4; ++$i) {
            $parallel->add($callback);
        }
        $res = $parallel->wait();
        $this->assertEquals(count($res), 4);

        for ($i = 0; $i < 4; ++$i) {
            $parallel->add($callback);
        }
        $res = $parallel->wait();
        $this->assertEquals(count($res), 8);
    }

    public function testParallelClear()
    {
        $parallel = new Parallel();
        $callback = function () {
            return 1;
        };
        for ($i = 0; $i < 4; ++$i) {
            $parallel->add($callback);
        }
        $res = $parallel->wait();
        $parallel->clear();
        $this->assertEquals(count($res), 4);

        for ($i = 0; $i < 4; ++$i) {
            $parallel->add($callback);
        }
        $res = $parallel->wait();
        $parallel->clear();
        $this->assertEquals(count($res), 4);
    }

    public function testParallelKeys()
    {
        $parallel = new Parallel();
        $callback = function () {
            return 1;
        };
        for ($i = 0; $i < 4; ++$i) {
            $parallel->add($callback);
        }
        $res = $parallel->wait();
        $parallel->clear();
        $this->assertSame([1, 1, 1, 1], $res);

        for ($i = 0; $i < 4; ++$i) {
            $parallel->add($callback, 'id_' . $i);
        }
        $res = $parallel->wait();
        $parallel->clear();
        $this->assertSame(['id_0' => 1, 'id_1' => 1, 'id_2' => 1, 'id_3' => 1], $res);

        for ($i = 0; $i < 4; ++$i) {
            $parallel->add($callback, $i - 1);
        }
        $res = $parallel->wait();
        $parallel->clear();
        $this->assertSame([-1 => 1, 0 => 1, 1 => 1, 2 => 1], $res);

        $parallel->add($callback, 1.0);
        $res = $parallel->wait();
        $parallel->clear();
        $this->assertSame([1.0 => 1], $res);
    }

    public function testParallelThrows()
    {
        $parallel = new Parallel();
        $err = function () {
            Coroutine::sleep(0.001);
            throw new RuntimeException('something bad happened');
        };
        $ok = function () {
            Coroutine::sleep(0.001);
            return 1;
        };
        $parallel->add($err);
        for ($i = 0; $i < 4; ++$i) {
            $parallel->add($ok);
        }
        $this->expectException(ParallelExecutionException::class);
        $res = $parallel->wait();
    }

    public function testParallelResultsAndThrows()
    {
        $parallel = new Parallel();

        $err = function () {
            Coroutine::sleep(0.001);
            throw new RuntimeException('something bad happened');
        };
        $parallel->add($err);

        $ids = [1 => uniqid(), 2 => uniqid(), 3 => uniqid(), 4 => uniqid()];
        foreach ($ids as $id) {
            $parallel->add(function () use ($id) {
                Coroutine::sleep(0.001);
                return $id;
            });
        }

        try {
            $parallel->wait();
            throw new RuntimeException();
        } catch (ParallelExecutionException $exception) {
            foreach (['Detecting', 'RuntimeException', '#0'] as $keyword) {
                $this->assertTrue(str_contains($exception->getMessage(), $keyword));
            }

            $result = $exception->getResults();
            $this->assertEquals($ids, $result);

            $throwables = $exception->getThrowables();
            $this->assertTrue(count($throwables) === 1);
            $this->assertSame('something bad happened', $throwables[0]->getMessage());
        }
    }

    public function testParallelCount()
    {
        $parallel = new Parallel();
        $id = 0;
        $parallel->add(static function () use (&$id) {
            ++$id;
        });
        $parallel->add(static function () use (&$id) {
            ++$id;
        });
        $this->assertSame(2, $parallel->count());
        $parallel->wait();
        $this->assertSame(2, $parallel->count());
        $this->assertSame(2, $id);
        $parallel->wait();
        $this->assertSame(2, $parallel->count());
        $this->assertSame(4, $id);
    }

    public function testTheResultSort()
    {
        $res = parallel(['a' => function () {
            usleep(1000);
            return 1;
        }, 'b' => function () {
            return 2;
        }]);

        $this->assertSame(['a' => 1, 'b' => 2], $res);

        $res = parallel(['a' => function () {
            usleep(1000);
            return 1;
        }, 'b' => function () {
        }]);

        $this->assertSame(['a' => 1, 'b' => null], $res);
    }

    public function testThrowExceptionInParallel()
    {
        try {
            parallel([
                static function () {
                    throw new Exception();
                },
            ]);
        } catch (ParallelExecutionException $exception) {
            /** @var Throwable $exception */
            $exception = $exception->getThrowables()[0];
            $traces = $exception->getTrace();
            ob_start();
            var_dump($traces);
            $content = ob_get_clean();
            $this->assertStringNotContainsString('*RECURSION*', $content);
        }
    }

    public function returnCoId()
    {
        return Coroutine::id();
    }
}
