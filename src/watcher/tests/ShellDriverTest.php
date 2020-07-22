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
use Hyperf\Watcher\Driver\ShellDriver;
use Hyperf\Watcher\Option;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class ShellDriverTest extends TestCase
{
    public function testSpeed()
    {
        $config = new Config([
            'watcher' => [
                'driver' => ShellDriver::class,
                'watch' => [
                    'dir' => ['test', 'vendor'],
                    'scan_interval' => 2000,
                ],
            ]
        ]);

        $option = new Option($config, [], []);
        $this->assertSame(ShellDriver::class, $option->getDriver());

        if ($option->getDriver() == ShellDriver::class) {
            $driver = new ShellDriver($option);
            $updateFiles = [];
            // circle 100 times
            $times = 100;

            $filesystem = new \Hyperf\Utils\Filesystem\Filesystem();


            $method = new \ReflectionMethod(ShellDriver::class, 'shellWatch');
            $method->setAccessible(true);

            while ($times--) {
                $filesystem->append(BASE_PATH . '/test/watch01.php', 'watch01');
                $filesystem->append(BASE_PATH . '/test/watch02.php', 'watch02');

                $sTime = microtime(true);

                $out = $method->invokeArgs($driver, [$updateFiles, $option->getScanInterval()]);

                $execTime = microtime(true) - $sTime;

                $this->assertLessThan(1, $execTime, 'more than 1 second');
                $this->assertSame(2, count($out['update_files']));
            }
        }
    }
}
