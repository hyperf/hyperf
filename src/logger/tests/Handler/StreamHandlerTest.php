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

namespace HyperfTest\Logger\Handler;

use DateTimeImmutable;
use Hyperf\Logger\Handler\StreamHandler;
use LogicException;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use stdClass;
use UnexpectedValueException;

/**
 * @internal
 * @coversNothing
 */
#[CoversClass(StreamHandler::class)]
class StreamHandlerTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/hyperf_stream_handler_test_' . uniqid();
        mkdir($this->tmpDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }

    public function testWriteToValidFile(): void
    {
        $file = $this->tmpDir . '/test.log';
        $handler = new StreamHandler($file);
        $handler->handle($this->createRecord('Hello World'));
        $handler->close();

        $this->assertFileExists($file);
        $this->assertStringContainsString('Hello World', file_get_contents($file));
    }

    /**
     * The main purpose of the rewrite: verify that StreamHandler does not
     * replace the global error handler during write operations.
     * In Swoole coroutine environments, the original Monolog StreamHandler's
     * set_error_handler/restore_error_handler could be interrupted by coroutine
     * switching, leaving the framework's default error handler replaced.
     */
    public function testErrorHandlerNotReplacedDuringWrite(): void
    {
        $file = $this->tmpDir . '/test_error_handler.log';

        // Set a custom error handler to verify it remains active after StreamHandler writes
        $marker = new stdClass();
        $marker->called = false;
        set_error_handler(function () use ($marker) {
            $marker->called = true;
            return true;
        });

        try {
            $handler = new StreamHandler($file);
            $handler->handle($this->createRecord('Test error handler preservation'));
            $handler->close();

            // Trigger a warning to verify our custom error handler is still active
            $marker->called = false;
            trigger_error('StreamHandlerTest probe', E_USER_WARNING);
            $this->assertTrue($marker->called, 'Custom error handler should still be active after StreamHandler writes');
        } finally {
            restore_error_handler();
        }
    }

    /**
     * When fopen fails (e.g. directory doesn't exist), the handler should
     * still throw UnexpectedValueException. The error message may differ from
     * the original Monolog version (which includes the PHP warning details),
     * but the exception type and core message should remain the same.
     */
    public function testFopenFailureThrowsUnexpectedValueException(): void
    {
        $file = '/nonexistent_root_dir_xyz/impossible/path/test.log';
        $handler = new StreamHandler($file);

        // Suppress PHP warnings from mkdir/fopen so we can test the exception
        set_error_handler(function () {
            return true;
        });

        try {
            $this->expectException(UnexpectedValueException::class);
            $this->expectExceptionMessage('could not be opened');
            $handler->handle($this->createRecord());
        } finally {
            restore_error_handler();
        }
    }

    /**
     * When fwrite throws a Throwable, the handler should catch it and throw
     * UnexpectedValueException. Unlike the original Monolog version which uses
     * set_error_handler to detect fwrite warnings, the Hyperf version uses
     * try/catch(Throwable) to handle write failures.
     */
    public function testWriteFailureThrowsUnexpectedValueException(): void
    {
        $file = $this->tmpDir . '/test_write_fail.log';

        $handler = new class($file) extends StreamHandler {
            protected function streamWrite($stream, LogRecord $record): void
            {
                throw new RuntimeException('Simulated write failure');
            }
        };

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Writing to the log file failed');

        $handler->handle($this->createRecord());
    }

    /**
     * When writing to a file URL fails, the handler should close the stream
     * and retry once. If the retry succeeds, the write should complete normally.
     */
    public function testWriteFailureRetriesOnFileUrl(): void
    {
        $file = $this->tmpDir . '/test_retry.log';
        $counter = new stdClass();
        $counter->attempts = 0;

        $handler = new class($file, $counter) extends StreamHandler {
            public function __construct(string $file, private stdClass $counter)
            {
                parent::__construct($file);
            }

            protected function streamWrite($stream, LogRecord $record): void
            {
                ++$this->counter->attempts;
                if ($this->counter->attempts === 1) {
                    throw new RuntimeException('First write fails');
                }
                parent::streamWrite($stream, $record);
            }
        };

        $handler->handle($this->createRecord('Retry success'));
        $handler->close();

        $this->assertSame(2, $counter->attempts);
        $this->assertFileExists($file);
        $this->assertStringContainsString('Retry success', file_get_contents($file));
    }

    /**
     * When writing fails twice (initial + retry), the handler should throw
     * UnexpectedValueException without further retries.
     */
    public function testWriteFailureThrowsAfterRetryExhausted(): void
    {
        $file = $this->tmpDir . '/test_retry_exhausted.log';

        $handler = new class($file) extends StreamHandler {
            protected function streamWrite($stream, LogRecord $record): void
            {
                throw new RuntimeException('Persistent write failure');
            }
        };

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Writing to the log file failed');

        $handler->handle($this->createRecord());
    }

    public function testDirectoryAutoCreation(): void
    {
        $dir = $this->tmpDir . '/nested/deep/dir';
        $file = $dir . '/test.log';

        $this->assertDirectoryDoesNotExist($dir);

        $handler = new StreamHandler($file);
        $handler->handle($this->createRecord('Nested directory test'));
        $handler->close();

        $this->assertDirectoryExists($dir);
        $this->assertFileExists($file);
        $this->assertStringContainsString('Nested directory test', file_get_contents($file));
    }

    public function testWriteWithResourceStream(): void
    {
        $stream = fopen('php://memory', 'r+');
        $handler = new StreamHandler($stream);
        $handler->handle($this->createRecord('Memory stream test'));

        rewind($stream);
        $content = stream_get_contents($stream);
        $this->assertStringContainsString('Memory stream test', $content);

        fclose($stream);
    }

    public function testWriteAfterCloseWithResourceStreamThrowsLogicException(): void
    {
        $stream = fopen('php://memory', 'r+');
        $handler = new StreamHandler($stream);
        $handler->close();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Missing stream url');

        $handler->handle($this->createRecord());
    }

    public function testClose(): void
    {
        $file = $this->tmpDir . '/test_close.log';
        $handler = new StreamHandler($file);
        $handler->handle($this->createRecord());

        $this->assertNotNull($handler->getStream());
        $handler->close();
        $this->assertNull($handler->getStream());
    }

    public function testResetClosesStreamForFileUrl(): void
    {
        $file = $this->tmpDir . '/test_reset.log';
        $handler = new StreamHandler($file);
        $handler->handle($this->createRecord());

        $this->assertNotNull($handler->getStream());
        $handler->reset();
        $this->assertNull($handler->getStream());
    }

    public function testGetUrl(): void
    {
        $file = $this->tmpDir . '/test_url.log';
        $handler = new StreamHandler($file);

        $this->assertSame($file, $handler->getUrl());
    }

    public function testGetUrlReturnsNullForResourceStream(): void
    {
        $stream = fopen('php://memory', 'r+');
        $handler = new StreamHandler($stream);

        $this->assertNull($handler->getUrl());

        fclose($stream);
    }

    public function testStreamChunkSize(): void
    {
        $handler = new StreamHandler($this->tmpDir . '/test_chunk.log');
        $this->assertGreaterThan(0, $handler->getStreamChunkSize());
    }

    private function removeDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDir($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function createRecord(string $message = 'test message', Level $level = Level::Info): LogRecord
    {
        return new LogRecord(
            datetime: new DateTimeImmutable(),
            channel: 'test',
            level: $level,
            message: $message,
        );
    }
}
