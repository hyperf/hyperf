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
namespace Hyperf\Nano\Factory;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ExceptionHandlerFactory
{
    public function create(\Closure $closure): ExceptionHandler
    {
        return new class($closure) extends ExceptionHandler {
            /**
             * @var \Closure
             */
            private $closure;

            public function __construct(\Closure $closure)
            {
                $this->closure = $closure;
            }

            public function handle(Throwable $throwable, ResponseInterface $response)
            {
                return call($this->closure, [$throwable, $response]);
            }

            public function isValid(Throwable $throwable): bool
            {
                return true;
            }
        };
    }
}
