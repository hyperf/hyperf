<?php

declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

class WaitGroupTest extends TestCase
{
    public function testWaitNotWait()
    {
        \Swoole\Coroutine::create(function () {
            $wg = new \Hyperf\Utils\WaitGroup();
            $wg->add();
            $wg->add();
            $result = [];
            
            $i = 2;
            while ($i--) {
                \Swoole\Coroutine::create(function () use ($wg, $i, &$result) {
                    \Swoole\Coroutine::sleep(1);
                    array_push($result, true);
                    $wg->done();
                });
            }
            
            $wg->wait();
            $this->assertEquals(count($result), 2);
            
            
            $wg->add();
            $wg->add();
            $result = [];
            $i = 2;
            while ($i--) {
                \Swoole\Coroutine::create(function () use ($wg, $i, &$result) {
                    \Swoole\Coroutine::sleep(1);
                    array_push($result, true);
                    $wg->done();
                });
            }
            
            $wg->wait();
            $this->assertEquals(count($result), 2);
            
            \Swoole\Coroutine::sleep(2);
        });
    }
}