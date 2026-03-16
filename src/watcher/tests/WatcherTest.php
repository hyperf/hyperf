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

namespace HyperfTest\Watcher;

use Hyperf\Config\Config;
use Hyperf\Watcher\Option;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversNothing]
class WatcherTest extends TestCase
{
    public function testOption()
    {
        $config = new Config([
            'watcher' => [
                'driver' => 'xxx',
                'watch' => [
                    'scan_interval' => 1500,
                ],
            ],
        ]);

        $option = new Option($config->get('watcher'), ['src'], []);

        $this->assertSame('xxx', $option->getDriver());
        $this->assertSame(['app', 'config', 'src'], $option->getWatchDir());
        $this->assertSame(['.env'], $option->getWatchFile());
        $this->assertSame(1500, $option->getScanInterval());
        $this->assertSame(1.5, $option->getScanIntervalSeconds());
    }

    protected function getContainer()
    {
    }
}
