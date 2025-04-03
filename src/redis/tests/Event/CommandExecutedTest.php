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

namespace HyperfTest\Redis\Event;

use Exception;
use Hyperf\Redis\Event\CommandExecuted;
use Hyperf\Redis\RedisConnection;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
#[CoversClass(CommandExecuted::class)]
class CommandExecutedTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testConstructor()
    {
        $command = 'GET';
        $parameters = ['key1'];
        $time = 0.1;
        $connection = Mockery::mock(RedisConnection::class);
        $connectionName = 'default';
        $result = 'value1';
        $throwable = null;

        $event = new CommandExecuted(
            $command,
            $parameters,
            $time,
            $connection,
            $connectionName,
            $result,
            $throwable
        );

        $this->assertSame($command, $event->command);
        $this->assertSame($parameters, $event->parameters);
        $this->assertSame($time, $event->time);
        $this->assertSame($connection, $event->connection);
        $this->assertSame($connectionName, $event->connectionName);
        $this->assertSame($result, $event->result);
        $this->assertSame($throwable, $event->throwable);
    }

    public function testFormatCommandWithSimpleParameters()
    {
        $command = 'GET';
        $parameters = ['key1'];
        $connection = Mockery::mock(RedisConnection::class);
        $event = new CommandExecuted(
            $command,
            $parameters,
            0.1,
            $connection,
            'default',
            'value1',
            null
        );

        $this->assertSame('GET key1', $event->getFormatCommand());
    }

    public function testFormatCommandWithArrayParameters()
    {
        $command = 'HMSET';
        $parameters = ['hash1', ['field1' => 'value1', 'field2' => 'value2']];
        $connection = Mockery::mock(RedisConnection::class);
        $event = new CommandExecuted(
            $command,
            $parameters,
            0.1,
            $connection,
            'default',
            true,
            null
        );

        $this->assertSame('HMSET hash1 field1 value1 field2 value2', $event->getFormatCommand());
    }

    public function testFormatCommandWithNestedArrayParameters()
    {
        $command = 'COMPLEX';
        $parameters = [
            'key1',
            [
                'field1' => ['subfield1' => 'value1'],
                'field2' => 'value2',
            ],
        ];
        $connection = Mockery::mock(RedisConnection::class);
        $event = new CommandExecuted(
            $command,
            $parameters,
            0.1,
            $connection,
            'default',
            true,
            null
        );

        $this->assertSame('COMPLEX key1 field1 {"subfield1":"value1"} field2 value2', $event->getFormatCommand());
    }

    public function testWithThrowable()
    {
        $command = 'GET';
        $parameters = ['key1'];
        $connection = Mockery::mock(RedisConnection::class);
        $throwable = new Exception('Test exception');
        $event = new CommandExecuted(
            $command,
            $parameters,
            0.1,
            $connection,
            'default',
            null,
            $throwable
        );

        $this->assertSame($throwable, $event->throwable);
    }
}
