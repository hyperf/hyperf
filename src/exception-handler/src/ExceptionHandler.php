<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace Hyperf\ExceptionHandler;

use Psr\Http\Message\ResponseInterface;
use Throwable;

abstract class ExceptionHandler
{
    /**
     * Determine if the exception should propagate to next handler.
     *
     * @var bool
     */
    protected $propagationStopped = false;

    /**
     * Handle the exception, and return the specified result.
     */
    abstract public function handle(Throwable $throwable, ResponseInterface $response);

    /**
     * Determine if the current exception handler should handle the exception,.
     *
     * @return bool
     *              If return true, then this exception handler will handle the exception,
     *              If return false, then delegate to next handler
     */
    abstract public function isValid(Throwable $throwable): bool;

    /**
     * Stop propagate the exception to next handler.
     */
    public function stopPropagation(): bool
    {
        $this->propagationStopped = true;
        return $this->propagationStopped;
    }

    /**
     * Is propagation stopped ?
     * This will typically only be used by the handler to determine if the
     * provious handler halted propagation.
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}
