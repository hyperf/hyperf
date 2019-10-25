<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\HttpServer\Exception\Handler;

use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class HttpExceptionHandler extends ExceptionHandler
{
    protected $logger;

    protected $formatter;

    public function __construct(StdoutLoggerInterface $logger, FormatterInterface $formatter)
    {
        $this->logger = $logger;
        $this->formatter = $formatter;
    }

    /**
     * Handle the exception, and return the specified result.
     */
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->logger->warning($this->formatter->format($throwable));

        return $response->withStatus($throwable->getCode())->withBody(new SwooleStream($throwable->getMessage()));
    }

    /**
     * Determine if the current exception handler should handle the exception,.
     *
     * @return bool
     *              If return true, then this exception handler will handle the exception,
     *              If return false, then delegate to next handler
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
