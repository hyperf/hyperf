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
namespace HyperfTest\Nats;

use Doctrine\Instantiator\Instantiator;
use Hyperf\Nats\Driver\NatsDriver;
use Hyperf\Support\Reflection\ClassInvoker;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class NatsDriverTest extends TestCase
{
    public function testGetMaxIdleTime()
    {
        $instantiator = new Instantiator();
        $driver = new ClassInvoker($instantiator->instantiate(NatsDriver::class));
        $time = $driver->getMaxIdleTime([
            'driver' => \Hyperf\Nats\Driver\NatsDriver::class,
            'encoder' => \Hyperf\Nats\Encoders\JSONEncoder::class,
            'timeout' => 10.0,
            'options' => [
                'host' => '127.0.0.1',
                'port' => 4222,
                'user' => 'nats',
                'pass' => 'nats',
                'lang' => 'php',
            ],
            'pool' => [
                'min_connections' => 1,
                'max_connections' => 10,
                'connect_timeout' => 10.0,
                'wait_timeout' => 3.0,
                'heartbeat' => -1,
                'max_idle_time' => 60,
            ],
        ]);

        $this->assertSame(10, $time);

        $time = $driver->getMaxIdleTime([
            'timeout' => -1,
            'pool' => [
                'max_idle_time' => 52,
            ],
        ]);

        $this->assertSame(52, $time);

        $time = $driver->getMaxIdleTime([
            'timeout' => 11,
            'pool' => [
                'max_idle_time' => 10,
            ],
        ]);

        $this->assertSame(10, $time);
    }
}
