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

namespace HyperfTest\LoadBalancer;

use Hyperf\Coordinator\Constants;
use Hyperf\Coordinator\CoordinatorManager;
use Hyperf\Engine\Channel;
use Hyperf\LoadBalancer\Node;
use Hyperf\LoadBalancer\Random;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class RandomTest extends TestCase
{
    public function testRandom()
    {
        $nodes = [
            new Node('127.0.0.1', 80),
            new Node('127.0.0.2', 81),
            new Node('127.0.0.3', 82),
        ];
        $random = new Random($nodes);
        $node = $random->select();
        $this->assertTrue(in_array($node, $nodes));
        $this->assertSame($nodes, $random->getNodes());
    }

    public function testAfterRefreshed()
    {
        $nodes = [
            new Node('127.0.0.1', 80),
            new Node('127.0.0.2', 81),
            new Node('127.0.0.3', 82),
        ];
        $random = new Random($nodes);
        $this->assertFalse($random->isAutoRefresh());
        $random->refresh(static function () {
            return [
                new Node('127.0.0.1', 80),
                new Node('127.0.0.4', 81),
                new Node('127.0.0.3', 81),
            ];
        }, 200);
        $chan = new Channel(1);
        $random->afterRefreshed('test', function ($old, $new) use ($chan) {
            $this->assertSame('127.0.0.1', $old[0]->host);
            $this->assertSame('127.0.0.4', $new[1]->host);
            $chan->push(true);
        });

        $chan->pop(-1);

        $this->assertTrue($random->isAutoRefresh());
        CoordinatorManager::until(Constants::WORKER_EXIT)->resume();
        CoordinatorManager::clear(Constants::WORKER_EXIT);
        $random->clearAfterRefreshedCallbacks();
    }

    public function testFunctionMakeExists()
    {
        $this->assertFalse(function_exists('make'));
        $this->assertTrue(function_exists('Hyperf\Support\make'));
    }
}
