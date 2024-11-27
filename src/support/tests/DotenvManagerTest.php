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

namespace HyperfTest\Support;

use Hyperf\Support\DotenvManager;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function Hyperf\Support\env;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class DotenvManagerTest extends TestCase
{
    public function testLoad()
    {
        DotenvManager::load([__DIR__ . '/envs/oldEnv']);

        $this->assertEquals('1.0', env('TEST_VERSION'));
        $this->assertTrue(env('OLD_FLAG'));
    }

    public function testReload()
    {
        DotenvManager::load([__DIR__ . '/envs/oldEnv']);
        $this->assertEquals('1.0', env('TEST_VERSION'));
        $this->assertTrue(env('OLD_FLAG'));

        DotenvManager::reload([__DIR__ . '/envs/newEnv'], true);
        $this->assertEquals('2.0', env('TEST_VERSION'));
        $this->assertNull(env('OLD_FLAG'));
        $this->assertTrue(env('NEW_FLAG'));
    }

    public function testGetEnvFromEnvironment()
    {
        DotenvManager::load([__DIR__ . '/envs/oldEnv']);

        $this->assertNotEquals('0.0.0', env('SW_VERSION'));
    }
}
