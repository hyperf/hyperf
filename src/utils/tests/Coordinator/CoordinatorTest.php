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
namespace HyperfTest\Utils\Coordinator;

use Hyperf\Utils\Coordinator\Coordinator;
use Hyperf\Utils\Coordinator\CoordinatorManager;
use Hyperf\Utils\WaitGroup;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CoordinatorTest extends TestCase
{
    public function testYield()
    {
        $coord = new Coordinator();
        $aborted = $coord->yield(0.001);
        $this->assertFalse($aborted);
    }

    public function testYieldResume()
    {
        $coord = new Coordinator();
        $wg = new WaitGroup();
        $wg->add();
        go(function () use ($coord, $wg) {
            $aborted = $coord->yield(10);
            $this->assertTrue($aborted);
            $wg->done();
        });
        $wg->add();
        go(function () use ($coord, $wg) {
            $aborted = $coord->yield(10);
            $this->assertTrue($aborted);
            $wg->done();
        });
        $coord->resume();
        $wg->wait();
    }

    public function testYieldResumeByCoordinator()
    {
        $id = uniqid();
        $coord = CoordinatorManager::until($id);
        $wg = new WaitGroup();
        $wg->add();
        go(function () use ($coord, $wg) {
            $aborted = $coord->yield(10);
            $this->assertTrue($aborted);
            $wg->done();
        });
        $wg->add();
        go(function () use ($coord, $wg) {
            $aborted = $coord->yield(10);
            $this->assertTrue($aborted);
            $wg->done();
        });
        \Hyperf\Coordinator\CoordinatorManager::until($id)->resume();
        $wg->wait();
    }
}
